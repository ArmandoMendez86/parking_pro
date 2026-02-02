<?php
// app/controladores/LoginControlador.php
require_once '../../config/configuracion.php';
require_once '../modelos/LoginModelo.php';

header('Content-Type: application/json; charset=utf-8');


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

        // ✅ Asegurar sesión activa
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // ✅ Vaciar sesión
        $_SESSION = [];

        // ✅ Borrar cookie de sesión
        if (ini_get("session.use_cookies")) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => 'Lax',
                ]
            );
        }

        // ✅ Destruir sesión
        session_destroy();

        // ============================================
        // ✅ Detectar si viene de fetch() o navegador
        // ============================================

        $esAjax = (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );

        // Si viene desde JS → responder JSON
        if ($esAjax) {
            json_ok([], "Sesión cerrada correctamente");
        }

        // Si viene como página normal → redirigir al login
        header("Location: " . URL_BASE . "vistas/login.php");
        exit;
    }




    if ($accion === 'login') {

        // ✅ En endpoints JSON, evita que warnings/notices se impriman y rompan el JSON
        // (en XAMPP es común tener display_errors=On)
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');

        // ✅ Leer JSON ANTES de iniciar sesión (aquí NO necesitas sesión)
        $data = post_json();
        $usuario  = str_clean($data['usuario'] ?? '');
        $password = (string)($data['password'] ?? '');
        $recordar = (bool)($data['recordar'] ?? false);

        if ($usuario === '' || $password === '') {
            json_fail('Usuario y contraseña son obligatorios');
        }
        if (strlen($usuario) < 3) json_fail('Usuario inválido');
        if (strlen($password) < 4) json_fail('Contraseña inválida');

        // ✅ Configurar "recordarme" ANTES de session_start()
        // IMPORTANTE: session_set_cookie_params debe ir antes de iniciar sesión
        if ($recordar) {
            $vida = 60 * 60 * 24 * 7; // 7 días

            // gc_maxlifetime (el warning te salía por hacerlo con sesión activa)
            ini_set('session.gc_maxlifetime', (string)$vida);

            // cookie de sesión con mayor duración
            session_set_cookie_params([
                'lifetime' => $vida,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                // 'secure' => true, // actívalo si estás en HTTPS
            ]);
        } else {
            // cookie normal de sesión (hasta cerrar navegador)
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                // 'secure' => true,
            ]);
        }

        // ✅ Ahora sí inicia sesión
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // ✅ Validar usuario
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

        // ✅ Buenas prácticas: regenerar ID de sesión al autenticar (evita session fixation)
        session_regenerate_id(true);

        // ✅ Setear sesión
        $_SESSION['auth'] = true;
        $_SESSION['usuario'] = (string)$u['usuario'];
        $_SESSION['nombre'] = (string)$u['nombre'];
        $_SESSION['rol'] = (string)$u['rol'];
        $_SESSION['usuario_id'] = (int)$u['id'];

        // ✅ Actualiza último acceso
        $modelo->actualizarUltimoAcceso((int)$u['id']);

        // ✅ Responder JSON limpio
        json_ok([
            'usuario' => $_SESSION['usuario'],
            'nombre'  => $_SESSION['nombre'],
            'rol'     => $_SESSION['rol']
        ], 'Acceso concedido');
    }


    json_fail('Acción no válida');
} catch (Exception $e) {
    json_fail('Error del servidor', [$e->getMessage()]);
}
