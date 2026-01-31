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

function round2($n)
{
    return round((float)$n, 2);
}

function normalizarTipoDescuento($tipo)
{
    $t = strtoupper(trim((string)$tipo));
    if ($t === 'PORCENTAJE' || $t === 'MONTO' || $t === 'HORAS') return $t;
    return '';
}

function calcularDescuentoMonto($tipo, $valor, $subtotal, $costoHora)
{
    $tipo = strtoupper(trim((string)$tipo));
    $valor = (float)$valor;
    $subtotal = (float)$subtotal;
    $costoHora = (float)$costoHora;

    if ($valor <= 0 || $subtotal <= 0) return 0.00;

    $m = 0.00;

    if ($tipo === 'PORCENTAJE') {
        $pct = min(100, max(0, $valor));
        $m = $subtotal * ($pct / 100);
    } elseif ($tipo === 'MONTO') {
        $m = $valor;
    } elseif ($tipo === 'HORAS') {
        $m = $valor * $costoHora;
    } else {
        $m = 0.00;
    }

    if (!is_finite($m)) $m = 0.00;
    $m = max(0, $m);
    $m = min($subtotal, $m);

    return round2($m);
}

/**
 * buscar_placa:
 * - Si es numérico => ticket ingreso
 * - Si no => placa (primero pension, si no ingreso)
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
                $ingreso['tolerancia_extra_minutos'],
                (float)($ingreso['extra_noche'] ?? 0),
                (int)($ingreso['tolerancia_entrada_minutos'] ?? 0)
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
                        'minutos_estancia' => (int)($calc['minutos_estancia'] ?? $calc['minutos_totales']),
                        'minutos_cobrables' => (int)($calc['minutos_cobrables'] ?? $calc['minutos_totales']),
                        'monto_tiempo' => (float)($calc['monto_tiempo'] ?? $calc['monto_total']),
                        'extra_noche' => (float)($calc['extra_noche'] ?? 0),
                        'extra_noche_veces' => (int)($calc['extra_noche_veces'] ?? 0),
                        'monto_total' => (float)$calc['monto_total'],
                        'hora_apertura' => $calc['hora_apertura'] ?? null,
                        'hora_cierre' => $calc['hora_cierre'] ?? null,
                        'sale_despues_cierre' => (int)($calc['sale_despues_cierre'] ?? 0),
                        'cobro_hasta' => $calc['cobro_hasta'] ?? null,
                        'gracia_aplicada_minutos' => (int)($calc['gracia_aplicada_minutos'] ?? 0),
                        'monto_pagado_adelantado' => (float)min((float)($ingreso['pago_adelantado_monto'] ?? 0), (float)$calc['monto_total']),
                        'monto_pendiente' => (float)max(0, round2((float)$calc['monto_total'] - (float)($ingreso['pago_adelantado_monto'] ?? 0)))
                    ]
                ]
            ]);
            exit;
        }

        $pension = $modelo->obtenerPensionVigentePorPlaca($termino);

        if ($pension) {
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Vehículo en pensión',
                'datos' => [
                    'tipo_resultado' => 'PENSION',
                    'pension' => $pension,
                    'calculo' => [
                        'fecha_salida' => $fecha_salida,
                        'minutos_totales' => 0,
                        'monto_total' => 0
                    ]
                ]
            ]);
            exit;
        }

        $ingreso = $modelo->obtenerIngresoActivoPorPlaca($termino);
        if (!$ingreso) {
            echo json_encode(['exito' => false, 'mensaje' => "No se encontró un vehículo activo con ese dato."]);
            exit;
        }

        $calc = $modelo->calcularCobro(
            $ingreso['fecha_ingreso'],
            $fecha_salida,
            $ingreso['costo_hora'],
            $ingreso['costo_fraccion_extra'],
            $ingreso['tolerancia_extra_minutos'],
            (float)($ingreso['extra_noche'] ?? 0),
            (int)($ingreso['tolerancia_entrada_minutos'] ?? 0)
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
                    'minutos_estancia' => (int)($calc['minutos_estancia'] ?? $calc['minutos_totales']),
                    'minutos_cobrables' => (int)($calc['minutos_cobrables'] ?? $calc['minutos_totales']),
                    'monto_tiempo' => (float)($calc['monto_tiempo'] ?? $calc['monto_total']),
                    'extra_noche' => (float)($calc['extra_noche'] ?? 0),
                    'extra_noche_veces' => (int)($calc['extra_noche_veces'] ?? 0),
                    'monto_total' => (float)$calc['monto_total'],
                    'hora_apertura' => $calc['hora_apertura'] ?? null,
                    'hora_cierre' => $calc['hora_cierre'] ?? null,
                    'sale_despues_cierre' => (int)($calc['sale_despues_cierre'] ?? 0),
                    'cobro_hasta' => $calc['cobro_hasta'] ?? null,
                    'gracia_aplicada_minutos' => (int)($calc['gracia_aplicada_minutos'] ?? 0),
                    'monto_pagado_adelantado' => (float)min((float)($ingreso['pago_adelantado_monto'] ?? 0), (float)$calc['monto_total']),
                    'monto_pendiente' => (float)max(0, round2((float)$calc['monto_total'] - (float)($ingreso['pago_adelantado_monto'] ?? 0)))
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
 * registrar_salida:
 * - Recalcula monto con base a tiempo + extra_noche (+ boleto perdido si aplica)
 * - Aplica descuento si viene
 * - Descuenta pago_adelantado (entrada) del PENDIENTE
 * - Guarda salida (monto_total = costo real) y monto_recibido = cobrado en salida
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'registrar_salida') {
    try {
        $id_ingreso = (int)obtenerValor('id_ingreso', 0, $payloadJson);
        $monto_recibido = (float)obtenerValor('monto_recibido', 0, $payloadJson);
        $usuario_cobro = obtenerValor('usuario_cobro', null, $payloadJson);

        $boleto_perdido = obtenerValor('boleto_perdido', false, $payloadJson);
        $boleto_perdido = filter_var($boleto_perdido, FILTER_VALIDATE_BOOLEAN);

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
            $ingreso['tolerancia_extra_minutos'],
            (float)($ingreso['extra_noche'] ?? 0),
            (int)($ingreso['tolerancia_entrada_minutos'] ?? 0)
        );

        $minutos_totales = (int)$calc['minutos_totales'];
        $monto_total_base = (float)$calc['monto_total'];
        $extra_noche = (float)($calc['extra_noche'] ?? 0.00);

        $extra_boleto_perdido = 0.00;
        if ($boleto_perdido) {
            $extra_boleto_perdido = (float)($ingreso['costo_boleto_perdido'] ?? 0);
        }

        $subtotal = round2($monto_total_base + $extra_boleto_perdido);

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

        // Pago adelantado registrado en la entrada (se descuenta del pendiente, no del costo real)
        $monto_pagado_adelantado = (float)($ingreso['pago_adelantado_monto'] ?? 0);
        if ($monto_pagado_adelantado < 0) $monto_pagado_adelantado = 0.00;
        $monto_pagado_adelantado = min($monto_total, $monto_pagado_adelantado);

        $monto_pendiente = round2(max(0, $monto_total - $monto_pagado_adelantado));

        if ($monto_pendiente > 0 && $monto_recibido < $monto_pendiente) {
            echo json_encode(['exito' => false, 'mensaje' => "Monto recibido insuficiente."]);
            exit;
        }

        $monto_cambio = ($monto_pendiente > 0) ? round2($monto_recibido - $monto_pendiente) : 0.00;
        if ($monto_pendiente == 0) {
            $monto_recibido = 0.00;
            $monto_cambio = 0.00;
        }

        $ok = $modelo->registrarSalidaIngreso(
            $id_ingreso,
            $fecha_salida,
            $minutos_totales,
            $monto_total,
            $extra_noche,
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
                    'minutos_estancia' => (int)($calc['minutos_estancia'] ?? $minutos_totales),
                    'minutos_cobrables' => (int)($calc['minutos_cobrables'] ?? $minutos_totales),
                    'monto_total_base' => round2($monto_total_base),
                    'monto_total' => round2($monto_total),
                    'monto_pagado_adelantado' => round2($monto_pagado_adelantado),
                    'monto_pendiente' => round2($monto_pendiente),
                    'extra_noche' => round2($extra_noche),
                    'extra_boleto_perdido' => round2($extra_boleto_perdido),
                    'subtotal' => round2($subtotal),
                    'descuento_monto' => round2($descuento_monto),
                    'monto_recibido' => round2($monto_recibido),
                    'monto_cambio' => round2($monto_cambio)
                ]
            ]);
            exit;
        }

        echo json_encode(['exito' => false, 'mensaje' => 'No fue posible registrar la salida.']);
        exit;

    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida.']);
