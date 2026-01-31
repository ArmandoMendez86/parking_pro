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

        if (empty($placa) || !$id_tarifa) {
            throw new Exception("Datos incompletos.");
        }

        if ($modelo->verificarVehiculoInterno($placa)) {
            echo json_encode(['exito' => false, 'mensaje' => "El vehículo $placa ya está dentro."]);
            exit;
        }

        // Generamos la fecha actual con el formato de MySQL pero usando la zona horaria de PHP
        $fecha_latam = date("Y-m-d H:i:s");

        if ($modelo->registrarIngreso($placa, $id_tarifa, $marca, $color, $fecha_latam)) {
            $ultimoId = $db->lastInsertId();
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Registro exitoso',
                'id_ingreso' => $ultimoId,
                'fecha_impresion' => date("d/m/Y H:i:s") // Formato para el ticket
            ]);
        } else {
            throw new Exception("Error al guardar.");
        }
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
}
