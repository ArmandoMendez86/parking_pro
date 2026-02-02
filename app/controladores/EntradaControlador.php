<?php
// Archivo: app/controladores/EntradaControlador.php

require_once '../../config/configuracion.php';
require_once '../modelos/EntradaModelo.php';
require_once '../modelos/SalidasModelo.php';

header('Content-Type: application/json');
$modelo = new EntradaModelo($db);
$accion = $_GET['accion'] ?? '';

function diaSemanaES(DateTime $d)
{
    $map = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    return $map[(int)$d->format('N')] ?? 'Lunes';
}

function construirDateTimeConHora(DateTime $fechaBase, $hora)
{
    [$h, $m, $s] = array_map('intval', explode(':', $hora));
    $d = clone $fechaBase;
    $d->setTime($h, $m, $s);
    return $d;
}

// Calcular monto sugerido para pago adelantado (autocálculo en entrada)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'calcular_adelanto') {
    try {
        $id_tarifa = (int)($_GET['id_tarifa'] ?? 0);
        $concepto  = trim((string)($_GET['concepto'] ?? ''));

        if ($id_tarifa <= 0) {
            throw new Exception("Tarifa inválida.");
        }

        $tarifa = $modelo->obtenerTarifaPorId($id_tarifa);
        if (!$tarifa) {
            throw new Exception("No se encontró la tarifa.");
        }

        $ahora = new DateTime();
        $dia = diaSemanaES($ahora);
        $horario = $modelo->obtenerHorarioPorDiaSemana($dia);

        $monto_tiempo = 0.00;
        $monto_extra_noche = 0.00;
        $monto_total = 0.00;

        $extra_noche_unitario = (float)($tarifa['extra_noche'] ?? 0.00);

        if ($concepto === 'SOLO_EXTRA_NOCHE') {

            $monto_extra_noche = $extra_noche_unitario;
            $monto_total = $monto_extra_noche;

        } elseif ($concepto === 'HORARIO_MAS_EXTRA_NOCHE') {

            // Tiempo restante hasta el cierre (si aplica) + 1x extra_noche
            if ($horario && (int)($horario['esta_abierto'] ?? 0) === 1 && !empty($horario['hora_apertura']) && !empty($horario['hora_cierre'])) {

                $open  = construirDateTimeConHora($ahora, $horario['hora_apertura']);
                $close = construirDateTimeConHora($ahora, $horario['hora_cierre']);

                if ($close <= $open) {
                    $close->modify('+1 day');
                }

                if ($ahora < $close) {
                    $salidasModelo = new SalidasModelo($GLOBALS['db']);

                    $calc = $salidasModelo->calcularCobro(
                        $ahora->format('Y-m-d H:i:s'),
                        $close->format('Y-m-d H:i:s'),
                        (float)($tarifa['costo_hora'] ?? 0),
                        (float)($tarifa['costo_fraccion_extra'] ?? 0),
                        (int)($tarifa['tolerancia_extra_minutos'] ?? 0),
                        0.00, // extra_noche se suma aparte (1x)
                        (int)($tarifa['tolerancia_entrada_minutos'] ?? 0)
                    );

                    $monto_tiempo = (float)($calc['monto_tiempo'] ?? 0.00);
                }
            }

            $monto_extra_noche = $extra_noche_unitario;
            $monto_total = round((float)$monto_tiempo + (float)$monto_extra_noche, 2);

        } else {
            // OTRO o vacío: no forzamos monto (manual)
            $monto_total = 0.00;
        }

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Cálculo listo',
            'datos' => [
                'concepto' => $concepto,
                'monto_sugerido' => round((float)$monto_total, 2),
                'desglose' => [
                    'monto_tiempo' => round((float)$monto_tiempo, 2),
                    'extra_noche' => round((float)$monto_extra_noche, 2)
                ]
            ]
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'registrar_entrada') {
    try {
        $placa    = strtoupper(trim($_POST['placa'] ?? ''));
        $id_tarifa = $_POST['id_tarifa'] ?? null;
        $marca     = trim($_POST['marca'] ?? '');
        $color     = trim($_POST['color'] ?? '');

        // Pago adelantado (opcional)
        $pago_activo = isset($_POST['pago_adelantado_activo']) ? (int)$_POST['pago_adelantado_activo'] : 0;
        $pago_adelantado_monto = $pago_activo ? (float)($_POST['pago_adelantado_monto'] ?? 0) : 0.00;
        if ($pago_adelantado_monto < 0) $pago_adelantado_monto = 0.00;

        $pago_adelantado_concepto = $pago_activo ? trim((string)($_POST['pago_adelantado_concepto'] ?? '')) : '';
        if ($pago_adelantado_concepto === '') $pago_adelantado_concepto = null;

        $pago_adelantado_nota = $pago_activo ? trim((string)($_POST['pago_adelantado_nota'] ?? '')) : '';
        if ($pago_adelantado_nota === '') $pago_adelantado_nota = null;
        if ($pago_adelantado_nota !== null && strlen($pago_adelantado_nota) > 120) $pago_adelantado_nota = substr($pago_adelantado_nota, 0, 120);

        $pago_adelantado_usuario = $_SESSION['usuario'] ?? null;

        if (empty($placa) || !$id_tarifa) {
            throw new Exception("Datos incompletos.");
        }

        if ($modelo->verificarVehiculoInterno($placa)) {
            echo json_encode(['exito' => false, 'mensaje' => "El vehículo $placa ya está dentro."]);
            exit;
        }

        $fecha_latam = date("Y-m-d H:i:s");

        if ($modelo->registrarIngreso($placa, $id_tarifa, $marca, $color, $fecha_latam, $pago_adelantado_monto, $pago_adelantado_concepto, $pago_adelantado_nota, $pago_adelantado_usuario)) {
            $ultimoId = $db->lastInsertId();
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Registro exitoso',
                'id_ingreso' => $ultimoId,
                'fecha_impresion' => date("d/m/Y H:i:s")
            ]);
        } else {
            throw new Exception("Error al guardar.");
        }
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
}
