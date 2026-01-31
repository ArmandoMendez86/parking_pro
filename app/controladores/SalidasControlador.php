<?php
require_once '../../config/configuracion.php';
require_once '../modelos/SalidasModelo.php';

header('Content-Type: application/json');

$modelo = new SalidasModelo($db);
$accion = $_GET['accion'] ?? '';

$entradaJson = file_get_contents('php://input');
$payloadJson = json_decode($entradaJson, true);
if (is_array($payloadJson)) {
    if (empty($accion) && !empty($payloadJson['accion'])) {
        $accion = $payloadJson['accion'];
    }
}

function obtenerValor($clave, $default = null, $payloadJson = null)
{
    if (is_array($payloadJson) && array_key_exists($clave, $payloadJson)) {
        return $payloadJson[$clave];
    }
    if (isset($_POST[$clave])) {
        return $_POST[$clave];
    }
    return $default;
}

function normalizarTipoDescuento($tipo)
{
    $tipo = strtoupper(trim((string)$tipo));
    $permitidos = ['PORCENTAJE', 'MONTO', 'HORAS'];
    return in_array($tipo, $permitidos, true) ? $tipo : '';
}

function round2($n)
{
    return round((float)$n, 2);
}

function calcularDescuentoMonto($tipo, $valor, $subtotal, $costo_hora)
{
    $tipo = normalizarTipoDescuento($tipo);
    $valor = (float)$valor;
    $subtotal = (float)$subtotal;
    $costo_hora = (float)$costo_hora;

    if ($tipo === '' || $valor <= 0 || $subtotal <= 0) return 0.00;

    $m = 0.00;

    if ($tipo === 'PORCENTAJE') {
        if ($valor > 100) $valor = 100;
        if ($valor < 0) $valor = 0;
        $m = $subtotal * ($valor / 100);
    } elseif ($tipo === 'MONTO') {
        $m = $valor;
    } elseif ($tipo === 'HORAS') {
        $m = $valor * $costo_hora;
    }

    if ($m < 0) $m = 0.00;
    if ($m > $subtotal) $m = $subtotal;

    return round2($m);
}

/**
 * BUSCAR PLACA/TICKET
 * Regla:
 * - Si es PLACA: 1) pensión vigente 2) si no, ingreso activo
 * - Si es NUMÉRICO: ticket => ingreso activo por id
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'buscar_placa') {
    try {
        $termino = strtoupper(trim((string)obtenerValor('termino', '', $payloadJson)));
        if (empty($termino)) {
            throw new Exception("Debe ingresar una placa o ticket.");
        }

        $fecha_salida = date("Y-m-d H:i:s");

        if (ctype_digit($termino)) {
            $ingreso = $modelo->obtenerIngresoActivoPorId((int)$termino);

            if (!$ingreso) {
                echo json_encode(['exito' => false, 'mensaje' => "No se encontró un ticket activo."]);
                exit;
            }

            $calc = $modelo->calcularCobro(
                $ingreso['fecha_ingreso'],
                $fecha_salida,
                $ingreso['costo_hora'],
                $ingreso['costo_fraccion_extra'],
                $ingreso['tolerancia_extra_minutos']
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Ingreso encontrado',
                'datos' => [
                    'tipo_resultado' => 'INGRESO',
                    'ingreso' => $ingreso,
                    'calculo' => [
                        'fecha_salida' => $fecha_salida,
                        'minutos_totales' => (int)$calc['minutos_totales'],
                        'monto_total' => (float)$calc['monto_total']
                    ]
                ]
            ]);
            exit;
        }

        $pension = $modelo->obtenerPensionVigentePorPlaca($termino);

        if ($pension) {
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Vehículo encontrado en PENSIÓN',
                'datos' => [
                    'tipo_resultado' => 'PENSION',
                    'pension' => $pension,
                    'calculo' => [
                        'fecha_salida' => $fecha_salida,
                        'minutos_totales' => 0,
                        'monto_total' => 0.00
                    ]
                ]
            ]);
            exit;
        }

        $ingreso = $modelo->obtenerIngresoActivoPorPlaca($termino);

        if (!$ingreso) {
            echo json_encode(['exito' => false, 'mensaje' => "No se encontró pensión vigente ni ingreso activo para $termino."]);
            exit;
        }

        $calc = $modelo->calcularCobro(
            $ingreso['fecha_ingreso'],
            $fecha_salida,
            $ingreso['costo_hora'],
            $ingreso['costo_fraccion_extra'],
            $ingreso['tolerancia_extra_minutos']
        );

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Ingreso encontrado',
            'datos' => [
                'tipo_resultado' => 'INGRESO',
                'ingreso' => $ingreso,
                'calculo' => [
                    'fecha_salida' => $fecha_salida,
                    'minutos_totales' => (int)$calc['minutos_totales'],
                    'monto_total' => (float)$calc['monto_total']
                ]
            ]
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
        exit;
    }
}

/**
 * REGISTRAR SALIDA (SOLO INGRESO)
 * + Soporta boleto_perdido: suma el extra de tarifas_vehiculos.costo_boleto_perdido al subtotal
 * + Soporta descuentos:
 *   - PORCENTAJE: subtotal * (valor/100)
 *   - MONTO: valor (cap al subtotal)
 *   - HORAS: valor * costo_hora (cap al subtotal)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'registrar_salida') {
    try {
        $id_ingreso = (int)obtenerValor('id_ingreso', 0, $payloadJson);
        $monto_recibido = (float)obtenerValor('monto_recibido', 0, $payloadJson);
        $usuario_cobro = obtenerValor('usuario_cobro', null, $payloadJson);

        $boleto_perdido = obtenerValor('boleto_perdido', false, $payloadJson);
        $boleto_perdido = filter_var($boleto_perdido, FILTER_VALIDATE_BOOLEAN);

        // descuento (opcional)
        $descuento_tipo = normalizarTipoDescuento(obtenerValor('descuento_tipo', '', $payloadJson));
        $descuento_valor = (float)obtenerValor('descuento_valor', 0, $payloadJson);
        $descuento_motivo = trim((string)obtenerValor('descuento_motivo', '', $payloadJson));
        if (strlen($descuento_motivo) > 255) $descuento_motivo = substr($descuento_motivo, 0, 255);

        if ($id_ingreso <= 0) {
            throw new Exception("Ticket inválido.");
        }

        if ($modelo->existeSalidaPorIngreso($id_ingreso)) {
            echo json_encode(['exito' => false, 'mensaje' => "Este ticket ya tiene salida registrada."]);
            exit;
        }

        $ingreso = $modelo->obtenerIngresoActivoPorId($id_ingreso);
        if (!$ingreso) {
            echo json_encode(['exito' => false, 'mensaje' => "El ticket no existe o ya fue finalizado."]);
            exit;
        }

        $fecha_salida = date("Y-m-d H:i:s");

        $calc = $modelo->calcularCobro(
            $ingreso['fecha_ingreso'],
            $fecha_salida,
            $ingreso['costo_hora'],
            $ingreso['costo_fraccion_extra'],
            $ingreso['tolerancia_extra_minutos']
        );

        $minutos_totales = (int)$calc['minutos_totales'];
        $monto_total_base = (float)$calc['monto_total'];

        $extra_boleto_perdido = 0.00;
        if ($boleto_perdido) {
            $extra_boleto_perdido = (float)($ingreso['costo_boleto_perdido'] ?? 0);
        }

        $subtotal = round2($monto_total_base + $extra_boleto_perdido);

        // si el tipo viene vacío o valor <=0 => no descuento
        if ($descuento_tipo === '' || $descuento_valor <= 0) {
            $descuento_tipo = null;
            $descuento_valor = null;
            $descuento_motivo = null;
            $descuento_monto = 0.00;
        } else {
            $descuento_monto = calcularDescuentoMonto(
                $descuento_tipo,
                $descuento_valor,
                $subtotal,
                (float)($ingreso['costo_hora'] ?? 0)
            );
        }

        $monto_total = round2(max(0, $subtotal - $descuento_monto));

        if ($monto_total > 0 && $monto_recibido < $monto_total) {
            echo json_encode(['exito' => false, 'mensaje' => "Monto recibido insuficiente."]);
            exit;
        }

        $monto_cambio = ($monto_total > 0) ? round2($monto_recibido - $monto_total) : 0.00;
        if ($monto_total == 0) {
            $monto_recibido = 0.00;
            $monto_cambio = 0.00;
        }

        $ok = $modelo->registrarSalidaIngreso(
            $id_ingreso,
            $fecha_salida,
            $minutos_totales,
            $monto_total,
            $monto_recibido,
            $monto_cambio,
            $usuario_cobro,
            $boleto_perdido,
            $descuento_tipo,
            $descuento_valor,
            $descuento_monto,
            $descuento_motivo
        );

        if ($ok) {
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Salida registrada correctamente',
                'datos' => [
                    'id_ingreso' => $id_ingreso,
                    'placa' => $ingreso['placa'],
                    'tipo_vehiculo' => $ingreso['tipo_vehiculo'],
                    'fecha_ingreso' => $ingreso['fecha_ingreso'],
                    'fecha_salida' => $fecha_salida,
                    'minutos_totales' => $minutos_totales,
                    'monto_total_base' => round2($monto_total_base),
                    'extra_boleto_perdido' => round2($extra_boleto_perdido),
                    'subtotal' => round2($subtotal),
                    'descuento_tipo' => $descuento_tipo,
                    'descuento_valor' => $descuento_valor,
                    'descuento_monto' => round2($descuento_monto),
                    'descuento_motivo' => $descuento_motivo,
                    'boleto_perdido' => $boleto_perdido ? 1 : 0,
                    'monto_total' => round2($monto_total),
                    'monto_recibido' => round2($monto_recibido),
                    'monto_cambio' => round2($monto_cambio),
                    'fecha_impresion' => date("d/m/Y H:i:s")
                ]
            ]);
            exit;
        }

        throw new Exception("Error al guardar la salida.");
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida o método no permitido.']);
