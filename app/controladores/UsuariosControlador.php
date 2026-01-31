<?php
// Archivo: app/controladores/UsuariosControlador.php

require_once '../../config/configuracion.php';
require_once '../modelos/UsuariosModelo.php';

header('Content-Type: application/json');

$modelo = new UsuariosModelo($db);
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

function responder($exito, $mensaje, $datos = null, $errores = [])
{
    echo json_encode([
        'exito' => (bool)$exito,
        'mensaje' => (string)$mensaje,
        'datos' => $datos,
        'errores' => $errores
    ]);
    exit;
}

function validarRol($rol)
{
    $permitidos = ['ADMIN', 'CAJERO', 'OPERADOR'];
    return in_array($rol, $permitidos, true);
}

/* =========================
   LISTAR
   GET ?accion=listar&q=&rol=
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar') {
    try {
        $q = trim((string)($_GET['q'] ?? ''));
        $rol = trim((string)($_GET['rol'] ?? ''));
        if ($rol !== '' && !validarRol($rol)) $rol = '';

        $lista = $modelo->listar($q, $rol);
        responder(true, 'OK', ['usuarios' => $lista]);
    } catch (Exception $e) {
        responder(false, $e->getMessage());
    }
}

/* =========================
   OBTENER
   GET ?accion=obtener&id=1
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'obtener') {
    try {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) responder(false, 'ID inválido.');

        $u = $modelo->obtenerPorId($id);
        if (!$u) responder(false, 'Usuario no encontrado.');

        responder(true, 'OK', ['usuario' => $u]);
    } catch (Exception $e) {
        responder(false, $e->getMessage());
    }
}

/* =========================
   GUARDAR (crear/editar)
   POST JSON:
   {accion:'guardar', id?, nombre, usuario, rol, password?, activo}
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'guardar') {
    try {
        $id = (int)obtenerValor('id', 0, $payloadJson);
        $nombre = trim((string)obtenerValor('nombre', '', $payloadJson));
        $usuario = trim((string)obtenerValor('usuario', '', $payloadJson));
        $rol = trim((string)obtenerValor('rol', '', $payloadJson));
        $password = (string)obtenerValor('password', '', $payloadJson);
        $activo = obtenerValor('activo', true, $payloadJson);
        $activo = filter_var($activo, FILTER_VALIDATE_BOOLEAN);

        $errores = [];

        if (mb_strlen($nombre) < 3) $errores[] = 'Nombre mínimo 3 caracteres.';
        if (mb_strlen($nombre) > 80) $errores[] = 'Nombre máximo 80 caracteres.';
        if (mb_strlen($usuario) < 3) $errores[] = 'Usuario mínimo 3 caracteres.';
        if (mb_strlen($usuario) > 50) $errores[] = 'Usuario máximo 50 caracteres.';
        if (!validarRol($rol)) $errores[] = 'Rol inválido.';

        if ($id <= 0) {
            if (mb_strlen($password) < 4) $errores[] = 'Contraseña mínima 4 caracteres (al crear).';
        } else {
            if ($password !== '' && mb_strlen($password) < 4) $errores[] = 'Contraseña mínima 4 caracteres.';
        }

        if (!empty($errores)) {
            responder(false, 'Revisa el formulario.', null, $errores);
        }

        if ($modelo->existeUsuarioLogin($usuario, $id)) {
            responder(false, 'El usuario (login) ya existe.');
        }

        if ($id <= 0) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $nuevoId = $modelo->crear($nombre, $usuario, $hash, $rol, $activo);
            if (!$nuevoId) responder(false, 'No se pudo crear el usuario.');

            $u = $modelo->obtenerPorId($nuevoId);
            responder(true, 'Usuario creado.', ['usuario' => $u]);
        }

        // editar
        $ok = $modelo->actualizar($id, $nombre, $usuario, $rol, $activo);
        if (!$ok) responder(false, 'No se pudo actualizar el usuario.');

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $modelo->actualizarPassword($id, $hash);
        }

        $u = $modelo->obtenerPorId($id);
        responder(true, 'Usuario actualizado.', ['usuario' => $u]);
    } catch (Exception $e) {
        responder(false, $e->getMessage());
    }
}

/* =========================
   ELIMINAR
   POST JSON: {accion:'eliminar', id}
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'eliminar') {
    try {
        $id = (int)obtenerValor('id', 0, $payloadJson);
        if ($id <= 0) responder(false, 'ID inválido.');

        $ok = $modelo->eliminar($id);
        if (!$ok) responder(false, 'No se pudo eliminar (puede no existir).');

        responder(true, 'Usuario eliminado.');
    } catch (Exception $e) {
        responder(false, $e->getMessage());
    }
}

/* =========================
   TOGGLE ACTIVO
   POST JSON: {accion:'set_activo', id, activo}
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'set_activo') {
    try {
        $id = (int)obtenerValor('id', 0, $payloadJson);
        $activo = obtenerValor('activo', true, $payloadJson);
        $activo = filter_var($activo, FILTER_VALIDATE_BOOLEAN);

        if ($id <= 0) responder(false, 'ID inválido.');

        $ok = $modelo->setActivo($id, $activo);
        if (!$ok) responder(false, 'No se pudo actualizar el estado.');

        $u = $modelo->obtenerPorId($id);
        responder(true, 'Estado actualizado.', ['usuario' => $u]);
    } catch (Exception $e) {
        responder(false, $e->getMessage());
    }
}

responder(false, 'Acción no válida o método no permitido.');
