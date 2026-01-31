<?php
require_once '../../config/configuracion.php';
require_once '../modelos/PensionesModelo.php';

header('Content-Type: application/json');

$modelo = new PensionesModelo($db);
$accion = $_GET['accion'] ?? '';

/* =========================================
   Helpers
   ========================================= */
function responder($exito, $mensaje = '', $datos = null) {
    echo json_encode([
        'exito' => (bool)$exito,
        'mensaje' => $mensaje,
        'datos' => $datos
    ]);
    exit;
}

function post($k, $default = null) {
    return $_POST[$k] ?? $default;
}

/**
 * Regla simple para convertir "plan_nombre" a días.
 * Puedes extenderla cuando quieras (por ejemplo leyendo catálogo de planes).
 */
function inferirDiasPlan($planNombre) {
    $p = mb_strtolower(trim((string)$planNombre));

    if ($p === '') return 30;

    // Heurísticas comunes
    if (str_contains($p, 'mens')) return 30;
    if (str_contains($p, 'quinc')) return 15;
    if (str_contains($p, 'seman')) return 7;
    if (str_contains($p, 'diar')) return 1;

    // Si el usuario escribe "30 días", "45 dias", etc.
    if (preg_match('/(\d+)\s*d[ií]a/', $p, $m)) {
        $n = (int)$m[1];
        return $n > 0 ? $n : 30;
    }

    // Default
    return 30;
}

try {
    /* =========================================
       GET: listar
       ========================================= */
    if ($accion === 'listar') {
        $busqueda = $_GET['busqueda'] ?? '';
        $lista = $modelo->listarPensiones($busqueda);
        responder(true, 'OK', ['pensiones' => $lista]);
    }

    /* =========================================
       GET: obtener (incluye pagos)
       ========================================= */
    if ($accion === 'obtener') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) responder(false, 'ID inválido');

        $pension = $modelo->obtenerPension($id);
        if (!$pension) responder(false, 'No se encontró la pensión');

        $pagos = $modelo->listarPagosPorPension($id);
        responder(true, 'OK', ['pension' => $pension, 'pagos' => $pagos]);
    }

    /* =========================================
       POST: guardar
       - Upsert por placa:
         * Si pension_id viene vacío (alta) pero la placa ya existe -> actualiza esa pensión.
         * Evita duplicados.
       ========================================= */
    if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)post('pension_id', 0);

        $placa = strtoupper(trim((string)post('vehiculo_placa', '')));

        $datos = [
            'cliente_nombre'   => trim((string)post('cliente_nombre', '')),
            'cliente_telefono' => trim((string)post('cliente_telefono', '')),
            'vehiculo_placa'   => $placa,
            'vehiculo_tipo'    => trim((string)post('vehiculo_tipo', 'Automóvil')),
            'plan_nombre'      => trim((string)post('plan_tipo', '')),
            'monto_mxn'        => (float)post('monto_mxn', 0),
            'vigencia_inicio'  => (string)post('vigencia_inicio', ''),
            'vigencia_fin'     => (string)post('vigencia_fin', ''),
            'notas'            => trim((string)post('notas', '')),
            'esta_activa'      => isset($_POST['estatus_activa']) ? 1 : 0
        ];

        if ($datos['cliente_nombre'] === '' || $datos['vehiculo_placa'] === '') {
            responder(false, 'Completa al menos: Nombre del cliente y Placa.');
        }
        if ($datos['vigencia_inicio'] === '' || $datos['vigencia_fin'] === '') {
            responder(false, 'Define la vigencia (inicio y fin).');
        }

        $db->beginTransaction();

        // ✅ UPSERT POR PLACA:
        // si viene como "nuevo" (id=0) pero esa placa ya existe -> actualizar
        if ($id <= 0) {
            $existente = $modelo->obtenerPensionPorPlaca($datos['vehiculo_placa']);
            if ($existente && !empty($existente['id'])) {
                $id = (int)$existente['id'];
            }
        }

        if ($id > 0) {
            $exito = $modelo->actualizarPension($id, $datos);
            if (!$exito) {
                $db->rollBack();
                responder(false, 'No se pudo actualizar la pensión.');
            }
            $db->commit();
            responder(true, 'Pensión actualizada (upsert por placa)', ['id' => $id]);
        } else {
            // crear normal (si por alguna razón no existía)
            $nuevoId = $modelo->crearPension($datos);
            if ($nuevoId <= 0) {
                $db->rollBack();
                responder(false, 'No se pudo crear la pensión.');
            }
            $db->commit();
            responder(true, 'Pensión creada correctamente', ['id' => $nuevoId]);
        }
    }

    /* =========================================
       POST: eliminar
       ========================================= */
    if ($accion === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)post('id', 0);
        if ($id <= 0) responder(false, 'ID inválido');

        $db->beginTransaction();
        $ok = $modelo->eliminarPension($id);
        if (!$ok) {
            $db->rollBack();
            responder(false, 'No se pudo eliminar la pensión.');
        }
        $db->commit();
        responder(true, 'Pensión eliminada correctamente');
    }

    /* =========================================
       POST: registrar_pago
       - Inserta pago
       - Renueva vigencia automáticamente acumulando:
         * Si vigencia_fin >= hoy -> suma a vigencia_fin
         * Si ya venció -> suma desde hoy (y reinicia inicio a hoy)
       ========================================= */
    if ($accion === 'registrar_pago' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pensionId = (int)post('pension_id', 0);
        if ($pensionId <= 0) responder(false, 'ID de pensión inválido');

        $monto = (float)post('monto_mxn', 0);
        if ($monto <= 0) responder(false, 'Monto inválido');

        $pension = $modelo->obtenerPension($pensionId);
        if (!$pension) responder(false, 'No se encontró la pensión');

        // Si no mandas fecha, se usa ahora
        $fechaPago = post('fecha_pago', date('Y-m-d H:i:s'));

        $pago = [
            'fecha_pago' => $fechaPago,
            'monto_mxn' => $monto,
            'metodo_pago' => trim((string)post('metodo_pago', 'Efectivo')),
            'referencia' => trim((string)post('referencia', '')),
            'notas' => trim((string)post('notas', '')),
            'usuario' => trim((string)post('usuario', 'sistema'))
        ];

        // Días a sumar:
        // - Si el frontend manda dias_extension, lo respetamos
        // - Si no, inferimos por el plan_nombre guardado en la pensión
        $dias = (int)post('dias_extension', 0);
        if ($dias <= 0) $dias = inferirDiasPlan($pension['plan_nombre'] ?? '');

        $db->beginTransaction();

        $okPago = $modelo->registrarPagoPension($pensionId, $pago);
        if (!$okPago) {
            $db->rollBack();
            responder(false, 'No se pudo registrar el pago.');
        }

        $okRenovar = $modelo->renovarVigenciaAcumulando($pensionId, $dias);
        if (!$okRenovar) {
            $db->rollBack();
            responder(false, 'Se registró el pago pero no se pudo renovar la vigencia.');
        }

        $db->commit();

        $pensionActualizada = $modelo->obtenerPension($pensionId);
        $pagos = $modelo->listarPagosPorPension($pensionId);

        responder(true, "Pago registrado y vigencia renovada (+{$dias} días)", [
            'pension' => $pensionActualizada,
            'pagos' => $pagos
        ]);
    }

    responder(false, 'Acción no válida');

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    responder(false, $e->getMessage());
}
