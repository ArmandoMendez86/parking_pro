<?php
require_once '../../config/configuracion.php';
require_once '../modelos/EntradaModelo.php';

header('Content-Type: application/json');
$modelo = new EntradaModelo($db);
$accion = $_GET['accion'] ?? '';

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
