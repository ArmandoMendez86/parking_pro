<?php
require_once '../../config/configuracion.php';
require_once '../modelos/ConfiguracionModelo.php';
header('Content-Type: application/json');

$modelo = new ConfiguracionModelo($db);
$accion = $_GET['accion'] ?? '';

if ($accion === 'obtener_datos') {
    $config = $modelo->obtenerConfiguracionGlobal();
    $horarios = $db->query("SELECT * FROM horarios_operacion ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $tarifas = $db->query("SELECT * FROM tarifas_vehiculos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['exito' => true, 'datos' => ['config' => $config, 'horarios' => $horarios, 'tarifas' => $tarifas]]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'guardar_todo') {
    try {
        $db->beginTransaction();

        $modelo->guardarConfiguracionGlobal([
            'nombre' => $_POST['nombre_negocio'],
            'telefono' => $_POST['telefono_negocio'],
            'direccion' => $_POST['direccion'],
            'moneda' => $_POST['moneda'],
            'tolerancia' => $_POST['tolerancia_entrada'],
            'impresora' => $_POST['nombre_impresora'],
            'papel' => $_POST['papel_tipo'],
            'copias' => $_POST['copias'] ?? 1,
            'encabezado' => $_POST['encabezado_global'],
            'pie_e' => $_POST['pie_entrada'],
            'pie_s' => $_POST['pie_salida'],
            'v_nom' => isset($_POST['ver_nombre']) ? 1 : 0,
            'v_tel' => isset($_POST['ver_telefono']) ? 1 : 0,
            'v_dir' => isset($_POST['ver_direccion']) ? 1 : 0,
            'v_mar' => 1,
            'v_fol' => 1,
            'v_enc' => isset($_POST['ver_encabezado']) ? 1 : 0,
            'v_pe' => isset($_POST['ver_pie_e']) ? 1 : 0,
            'v_ps' => isset($_POST['ver_pie_s']) ? 1 : 0,
            'estilos' => $_POST['estilos_ticket']
        ]);

        if (isset($_POST['dia_nombre'])) {
            foreach ($_POST['dia_nombre'] as $i => $dia) {
                $abierto = isset($_POST['dia_activo'][$i]) ? 1 : 0;
                $modelo->actualizarHorarioDia($dia, $abierto, $_POST['abre'][$i], $_POST['cierra'][$i]);
            }
        }

        // ✅ BORRADO DE TARIFAS: se eliminan primero (las marcadas desde el JS)
        if (!empty($_POST['t_borrar']) && is_array($_POST['t_borrar'])) {
            foreach ($_POST['t_borrar'] as $id_borrar) {
                $id_borrar = (int)$id_borrar;
                if ($id_borrar > 0) $modelo->eliminarTarifa($id_borrar);
            }
        }

        if (isset($_POST['t_nombre'])) {
            foreach ($_POST['t_nombre'] as $i => $nombre) {
                // Enviamos el ID para actualizar en lugar de borrar
                $id_t = $_POST['t_id'][$i] ?? null;

                $modelo->sincronizarTarifa(
                    $id_t,
                    $nombre,
                    $_POST['t_hora'][$i],
                    $_POST['t_extra'][$i],
                    $_POST['t_tol_extra'][$i] ?? 0,
                    $_POST['t_perdido'][$i] ?? 0
                );
            }
        }

        $db->commit();
        echo json_encode(['exito' => true, 'mensaje' => 'Configuración guardada correctamente']);
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}
