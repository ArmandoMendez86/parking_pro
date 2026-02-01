<?php
// app/helpers/AuthHelper.php
// Helper reutilizable para proteger vistas y manejar sesión

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

class AuthHelper {

  // ✅ Verifica si hay sesión activa
  public static function estaLogueado() {
    return isset($_SESSION['auth']) && $_SESSION['auth'] === true;
  }

  // ✅ Retorna datos del usuario actual
  public static function usuario() {
    return $_SESSION['usuario'] ?? null;
  }

  public static function nombre() {
    return $_SESSION['nombre'] ?? null;
  }

  public static function rol() {
    return $_SESSION['rol'] ?? null;
  }

  // ✅ Protege cualquier vista (redirige al login)
  public static function protegerVista() {
    if (!self::estaLogueado()) {
      header("Location: " . (defined("URL_BASE") ? URL_BASE : "") . "/vistas/login.php");
      exit;
    }
  }

  // ✅ Protege por rol (ADMIN, CAJERO, OPERADOR)
  public static function protegerRol($rolesPermitidos = []) {
    if (!self::estaLogueado()) {
      self::protegerVista();
    }

    if (!in_array(self::rol(), $rolesPermitidos)) {
      echo "<h3 style='font-family:Inter;padding:30px;color:red;'>
              Acceso denegado: permisos insuficientes.
            </h3>";
      exit;
    }
  }

  // ✅ Cierra sesión completamente
  public static function logout() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
      );
    }

    session_destroy();

    header("Location: " . (defined("URL_BASE") ? URL_BASE : "") . "vistas/login/index.php");
    exit;
  }
}
