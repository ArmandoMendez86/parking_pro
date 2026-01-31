<?php
// Archivo: app/controladores/DashboardControlador.php
require_once '../../config/configuracion.php';
require_once '../modelos/DashboardModelo.php';

header('Content-Type: application/json');

$modelo = new DashboardModelo($db);
$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'metricas') {
    try {
        $datos = $modelo->obtenerMetricas();
        echo json_encode(['exito' => true, 'mensaje' => 'Métricas cargadas', 'datos' => $datos]);
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'config') {
    try {
        $datos = $modelo->obtenerConfig();
        echo json_encode(['exito' => true, 'mensaje' => 'Configuración cargada', 'datos' => $datos]);
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'config_guardar') {
    try {
        $nombre_negocio  = trim((string)($_POST['nombre_negocio'] ?? ''));
        $moneda_simbolo  = trim((string)($_POST['moneda_simbolo'] ?? ''));
        $nombre_impresora = trim((string)($_POST['nombre_impresora'] ?? ''));

        if ($nombre_negocio === '' || $moneda_simbolo === '') {
            throw new Exception("Datos incompletos.");
        }

        if (mb_strlen($nombre_negocio) > 150) $nombre_negocio = mb_substr($nombre_negocio, 0, 150);
        if (mb_strlen($moneda_simbolo) > 5) $moneda_simbolo = mb_substr($moneda_simbolo, 0, 5);
        if ($nombre_impresora !== '' && mb_strlen($nombre_impresora) > 100) $nombre_impresora = mb_substr($nombre_impresora, 0, 100);
        if ($nombre_impresora === '') $nombre_impresora = 'POS-80';

        $ok = $modelo->guardarConfig($nombre_negocio, $moneda_simbolo, $nombre_impresora);
        if (!$ok) {
            throw new Exception("Error al guardar.");
        }

        $datos = $modelo->obtenerConfig();
        echo json_encode(['exito' => true, 'mensaje' => 'Configuración guardada', 'datos' => $datos]);
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['exito' => false, 'mensaje' => 'Acción inválida.']);
