<?php
require_once '../../config/configuracion.php';
require_once '../modelos/ReportesModelo.php';

header('Content-Type: application/json');

$modelo = new ReportesModelo($db);
$accion = $_GET['accion'] ?? '';

function json_ok($datos = [], $mensaje = 'OK') {
    echo json_encode([
        'ok' => true,
        'exito' => true, // compat legado
        'mensaje' => $mensaje,
        'datos' => $datos
    ]);
    exit;
}

function json_fail($mensaje = 'Error', $errores = []) {
    echo json_encode([
        'ok' => false,
        'exito' => false, // compat legado
        'mensaje' => $mensaje,
        'errores' => $errores
    ]);
    exit;
}

function get_str($k, $default = '') {
    return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $default;
}

function get_int($k, $default = 0) {
    return isset($_GET[$k]) ? (int)$_GET[$k] : $default;
}

function is_date_yyyy_mm_dd($s) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
    $p = explode('-', $s);
    return checkdate((int)$p[1], (int)$p[2], (int)$p[0]);
}

function is_time_hh_mm($s) {
    if (!preg_match('/^\d{2}:\d{2}$/', $s)) return false;
    [$hh, $mm] = array_map('intval', explode(':', $s));
    return ($hh >= 0 && $hh <= 23) && ($mm >= 0 && $mm <= 59);
}

function build_rango_datetime_from_request() {
    $modo = get_str('modo_periodo', 'rango'); // rango | turno

    if ($modo === 'turno') {
        $fecha = get_str('turno_fecha', '');
        $h_ini = get_str('turno_hora_inicio', '08:00');
        $h_fin = get_str('turno_hora_fin', '16:00');

        if (!is_date_yyyy_mm_dd($fecha)) json_fail('Fecha de turno inválida');
        if (!is_time_hh_mm($h_ini) || !is_time_hh_mm($h_fin)) json_fail('Horas de turno inválidas');

        $desde = $fecha . ' ' . $h_ini . ':00';

        // Cruza medianoche si fin <= inicio (incluye caso 00:00)
        $cruza = ($h_fin === '00:00') || (strtotime($fecha . ' ' . $h_fin . ':00') <= strtotime($desde));
        if ($cruza) {
            $hasta_fecha = date('Y-m-d', strtotime($fecha . ' +1 day'));
            $hasta = $hasta_fecha . ' ' . $h_fin . ':00';
        } else {
            $hasta = $fecha . ' ' . $h_fin . ':00';
        }

        return [$desde, $hasta, 'turno'];
    }

    // rango
    $desde_f = get_str('desde_fecha', '');
    $hasta_f = get_str('hasta_fecha', '');
    $desde_h = get_str('desde_hora', '00:00');
    $hasta_h = get_str('hasta_hora', '23:59');

    if (!is_date_yyyy_mm_dd($desde_f) || !is_date_yyyy_mm_dd($hasta_f)) json_fail('Rango de fechas inválido');
    if (!is_time_hh_mm($desde_h) || !is_time_hh_mm($hasta_h)) json_fail('Rango de horas inválido');

    $desde = $desde_f . ' ' . $desde_h . ':00';
    $hasta = $hasta_f . ' ' . $hasta_h . ':59';

    if (strtotime($hasta) < strtotime($desde)) json_fail('El rango final no puede ser menor al inicial');

    return [$desde, $hasta, 'rango'];
}

try {

    if ($accion === 'obtener_usuarios') {
        $usuarios = $modelo->obtenerUsuariosActivos();
        json_ok(['usuarios' => $usuarios], 'Usuarios obtenidos');
    }

    if ($accion === 'corte_cajero') {
        [$desde, $hasta, $modo] = build_rango_datetime_from_request();
        $usuario = get_str('usuario', '');
        $metodo = get_str('metodo_pago', '');

        $resumen = $modelo->corteCajeroResumen($desde, $hasta, $usuario, $metodo);
        $metodos = $modelo->corteCajeroPorMetodo($desde, $hasta, $usuario, $metodo);
        $detalle = $modelo->corteCajeroDetalle($desde, $hasta, $usuario, $metodo);

        json_ok([
            'modo_periodo' => $modo,
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['usuario' => $usuario, 'metodo_pago' => $metodo],
            'resumen' => $resumen,
            'por_metodo' => $metodos,
            'detalle' => $detalle
        ], 'Corte por cajero generado');
    }

    if ($accion === 'corte_diario') {
        [$desde, $hasta, $modo] = build_rango_datetime_from_request();
        $usuario = get_str('usuario', '');
        $metodo = get_str('metodo_pago', '');

        $resumen_dias = $modelo->corteDiarioResumen($desde, $hasta, $usuario, $metodo);

        json_ok([
            'modo_periodo' => $modo,
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['usuario' => $usuario, 'metodo_pago' => $metodo],
            'dias' => $resumen_dias
        ], 'Corte diario generado');
    }

    if ($accion === 'descuentos') {
        [$desde, $hasta, $modo] = build_rango_datetime_from_request();
        $usuario = get_str('usuario', '');
        $tipo = get_str('descuento_tipo', ''); // PORCENTAJE | MONTO | HORAS
        $min_monto = get_str('min_descuento', ''); // decimal string

        $resumen = $modelo->descuentosResumen($desde, $hasta, $usuario, $tipo, $min_monto);
        $detalle = $modelo->descuentosDetalle($desde, $hasta, $usuario, $tipo, $min_monto);

        json_ok([
            'modo_periodo' => $modo,
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => [
                'usuario' => $usuario,
                'descuento_tipo' => $tipo,
                'min_descuento' => $min_monto
            ],
            'resumen' => $resumen,
            'detalle' => $detalle
        ], 'Reporte de descuentos generado');
    }

    if ($accion === 'boletos_perdidos') {
        [$desde, $hasta, $modo] = build_rango_datetime_from_request();
        $usuario = get_str('usuario', '');
        $metodo = get_str('metodo_pago', '');

        $resumen = $modelo->boletosPerdidosResumen($desde, $hasta, $usuario, $metodo);
        $detalle = $modelo->boletosPerdidosDetalle($desde, $hasta, $usuario, $metodo);

        json_ok([
            'modo_periodo' => $modo,
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['usuario' => $usuario, 'metodo_pago' => $metodo],
            'resumen' => $resumen,
            'detalle' => $detalle
        ], 'Reporte de boletos perdidos generado');
    }

    if ($accion === 'extra_noche') {
        [$desde, $hasta, $modo] = build_rango_datetime_from_request();
        $usuario = get_str('usuario', '');
        $metodo = get_str('metodo_pago', '');

        $resumen = $modelo->extraNocheResumen($desde, $hasta, $usuario, $metodo);
        $detalle = $modelo->extraNocheDetalle($desde, $hasta, $usuario, $metodo);

        json_ok([
            'modo_periodo' => $modo,
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['usuario' => $usuario, 'metodo_pago' => $metodo],
            'resumen' => $resumen,
            'detalle' => $detalle
        ], 'Reporte de extra noche generado');
    }

    if ($accion === 'anticipos') {
        $desde_f = get_str('desde_fecha', '');
        $hasta_f = get_str('hasta_fecha', '');
        if (!is_date_yyyy_mm_dd($desde_f) || !is_date_yyyy_mm_dd($hasta_f)) json_fail('Rango de fechas inválido');
        $desde = $desde_f . ' 00:00:00';
        $hasta = $hasta_f . ' 23:59:59';

        $usuario = get_str('usuario', ''); // pago_adelantado_usuario
        $concepto = get_str('concepto', '');

        $resumen = $modelo->anticiposResumen($desde, $hasta, $usuario, $concepto);
        $detalle = $modelo->anticiposDetalle($desde, $hasta, $usuario, $concepto);

        json_ok([
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['usuario' => $usuario, 'concepto' => $concepto],
            'resumen' => $resumen,
            'detalle' => $detalle
        ], 'Reporte de anticipos generado');
    }

    if ($accion === 'ocupacion') {
        $placa = get_str('placa', '');
        $id_tarifa = get_int('id_tarifa', 0);

        $resumen = $modelo->ocupacionResumen($placa, $id_tarifa);
        $detalle = $modelo->ocupacionDetalle($placa, $id_tarifa);

        json_ok([
            'filtros' => ['placa' => $placa, 'id_tarifa' => $id_tarifa],
            'resumen' => $resumen,
            'detalle' => $detalle
        ], 'Ocupación actual generada');
    }

    if ($accion === 'entradas_periodo') {
        $desde_f = get_str('desde_fecha', '');
        $hasta_f = get_str('hasta_fecha', '');
        if (!is_date_yyyy_mm_dd($desde_f) || !is_date_yyyy_mm_dd($hasta_f)) json_fail('Rango de fechas inválido');

        $desde = $desde_f . ' 00:00:00';
        $hasta = $hasta_f . ' 23:59:59';

        $id_tarifa = get_int('id_tarifa', 0);
        $usuario = get_str('usuario', '');

        $por_dia = $modelo->entradasPorDia($desde, $hasta, $id_tarifa, $usuario);
        $detalle = $modelo->entradasDetalle($desde, $hasta, $id_tarifa, $usuario);

        json_ok([
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['id_tarifa' => $id_tarifa, 'usuario' => $usuario],
            'por_dia' => $por_dia,
            'detalle' => $detalle
        ], 'Entradas por periodo generadas');
    }

    if ($accion === 'estancia_promedio') {
        [$desde, $hasta, $modo] = build_rango_datetime_from_request();
        $usuario = get_str('usuario', '');
        $id_tarifa = get_int('id_tarifa', 0);

        $resumen = $modelo->estanciaPromedioResumen($desde, $hasta, $id_tarifa, $usuario);
        $top = $modelo->estanciasMasLargas($desde, $hasta, $id_tarifa, $usuario);

        json_ok([
            'modo_periodo' => $modo,
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['id_tarifa' => $id_tarifa, 'usuario' => $usuario],
            'resumen' => $resumen,
            'top_largas' => $top
        ], 'Estancia promedio generada');
    }

    if ($accion === 'pensiones_activas') {
        $q = get_str('q', '');
        $items = $modelo->pensionesActivas($q);
        json_ok(['filtro' => ['q' => $q], 'detalle' => $items], 'Pensiones activas obtenidas');
    }

    if ($accion === 'pensiones_vencer') {
        $dias = get_int('dias', 7);
        if ($dias <= 0) $dias = 7;
        $items = $modelo->pensionesPorVencer($dias);
        json_ok(['filtro' => ['dias' => $dias], 'detalle' => $items], 'Pensiones por vencer obtenidas');
    }

    if ($accion === 'pagos_pensiones') {
        $desde_f = get_str('desde_fecha', '');
        $hasta_f = get_str('hasta_fecha', '');
        if (!is_date_yyyy_mm_dd($desde_f) || !is_date_yyyy_mm_dd($hasta_f)) json_fail('Rango de fechas inválido');

        $desde = $desde_f . ' 00:00:00';
        $hasta = $hasta_f . ' 23:59:59';

        $usuario = get_str('usuario', '');
        $metodo = get_str('metodo_pago', ''); // en pagos_pensiones
        $resumen = $modelo->pagosPensionesResumen($desde, $hasta, $usuario, $metodo);
        $detalle = $modelo->pagosPensionesDetalle($desde, $hasta, $usuario, $metodo);

        json_ok([
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => ['usuario' => $usuario, 'metodo_pago' => $metodo],
            'resumen' => $resumen,
            'detalle' => $detalle
        ], 'Pagos de pensiones generados');
    }

    json_fail('Acción no válida');

} catch (Exception $e) {
    json_fail('Error del servidor', [$e->getMessage()]);
}
