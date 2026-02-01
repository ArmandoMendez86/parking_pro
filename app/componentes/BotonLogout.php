<?php
// app/componentes/BotonLogout.php
// Botón reutilizable para cerrar sesión desde cualquier vista

if (!defined("URL_BASE")) {
  define("URL_BASE", "");
}
?>

<form method="POST" action="<?php echo URL_BASE; ?>/app/controladores/LoginControlador.php?accion=logout"
      style="display:inline;">
  
  <button type="submit"
          class="btn btn-outline-danger btn-lg d-flex align-items-center gap-2"
          style="border-radius:16px;">
    <i class="bi bi-box-arrow-right"></i>
    Cerrar sesión
  </button>
</form>
