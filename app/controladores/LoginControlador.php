<?php
// app/controladores/LoginControlador.php
require_once '../../config/configuracion.php';
require_once '../modelos/LoginModelo.php';

header('Content-Type: application/json');

$modelo = new LoginModelo($db);
$accion = $_GET['accion'] ?? '';

function json_ok($datos = [], $mensaje = 'OK')
{
    echo json_encode([
        'ok' => true,
        'mensaje' => $mensaje,
        'datos' => $datos
    ]);
    exit;
}

function json_fail($mensaje = 'Error', $errores = [])
{
    echo json_encode([
        'ok' => false,
        'mensaje' => $mensaje,
        'errores' => $errores
    ]);
    exit;
}

function post_json()
{
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function str_clean($v)
{
    return trim((string)$v);
}

function ensure_session()
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

try {

    if ($accion === 'estado') {
        ensure_session();
        $auth = isset($_SESSION['auth']) && $_SESSION['auth'] === true;

        json_ok([
            'autenticado' => $auth,
            'usuario' => $auth ? ($_SESSION['usuario'] ?? null) : null,
            'rol' => $auth ? ($_SESSION['rol'] ?? null) : null,
            'nombre' => $auth ? ($_SESSION['nombre'] ?? null) : null
        ], 'Estado de sesión');
    }

    if ($accion === 'logout') {

        ensure_session();

        // ✅ Limpiar sesión
        $_SESSION = [];

        // ✅ Borrar cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // ✅ Destruir sesión
        session_destroy();

        // ✅ Redirigir SIEMPRE al login
        header("Location: " . (defined("URL_BASE") ? URL_BASE : "") . "vistas/login.php");
        exit;
    }


    if ($accion === 'login') {
        ensure_session();

        $data = post_json();
        $usuario = str_clean($data['usuario'] ?? '');
        $password = (string)($data['password'] ?? '');
        $recordar = (bool)($data['recordar'] ?? false);

        if ($usuario === '' || $password === '') {
            json_fail('Usuario y contraseña son obligatorios');
        }
        if (strlen($usuario) < 3) json_fail('Usuario inválido');
        if (strlen($password) < 4) json_fail('Contraseña inválida');

        $u = $modelo->obtenerUsuarioPorUsuario($usuario);

        if (!$u) {
            json_fail('Credenciales incorrectas');
        }
        if ((int)$u['activo'] !== 1) {
            json_fail('Usuario inactivo');
        }

        $hash = (string)($u['password_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            json_fail('Credenciales incorrectas');
        }

        // sesión
        $_SESSION['auth'] = true;
        $_SESSION['usuario'] = (string)$u['usuario'];
        $_SESSION['nombre'] = (string)$u['nombre'];
        $_SESSION['rol'] = (string)$u['rol'];
        $_SESSION['usuario_id'] = (int)$u['id'];

        // Actualiza último acceso
        $modelo->actualizarUltimoAcceso((int)$u['id']);

        // “Recordarme”: sesión más larga (sin implementar token persistente en BD)
        // Si quieres recordarme REAL (multi-dispositivo), agregamos tabla sesiones_tokens.
        if ($recordar) {
            // 7 días
            ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24 * 7));
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + (60 * 60 * 24 * 7), $params["path"], $params["domain"], $params["secure"], true);
        }

        json_ok([
            'usuario' => $_SESSION['usuario'],
            'nombre' => $_SESSION['nombre'],
            'rol' => $_SESSION['rol']
        ], 'Acceso concedido');
    }

    json_fail('Acción no válida');
} catch (Exception $e) {
    json_fail('Error del servidor', [$e->getMessage()]);
}
