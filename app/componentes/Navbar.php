<?php
// app/componentes/Navbar.php
// Navbar PRO (glass) + botón cerrar sesión integrado

if (!defined("URL_BASE")) {
  define("URL_BASE", "");
}
?>

<style>
  /* ==========================================================
     NAVBAR PRO (glass) - mismo ADN visual
     ========================================================== */
  .navbar-pro {
    background: linear-gradient(180deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .03));
    border-bottom: 1px solid rgba(255, 255, 255, .12);
    box-shadow: 0 14px 34px rgba(0, 0, 0, .22);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }

  .brand-pro {
    color: rgba(255, 255, 255, .92) !important;
    text-decoration: none;
    user-select: none;
  }

  .brand-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .12);
    box-shadow: 0 10px 22px rgba(0, 0, 0, .18);
  }

  .brand-icon i {
    color: rgba(59, 130, 246, .95);
    font-size: 1.25rem;
  }

  .brand-text {
    letter-spacing: .01em;
  }

  .chip-mini {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .35rem .65rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .06);
    color: rgba(255, 255, 255, .86);
    font-size: .78rem;
    font-weight: 800;
  }

  .navlink-pro {
    border-radius: 14px;
    padding: .65rem .9rem !important;
    color: rgba(255, 255, 255, .84) !important;
    border: 1px solid transparent;
    background: transparent;
    font-weight: 800;
    letter-spacing: .01em;
    transition: all .15s ease;
    user-select: none;
  }

  .navlink-pro:hover {
    color: #fff !important;
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .12);
    transform: translateY(-1px);
  }

  .navlink-pro:active {
    transform: translateY(0px);
  }

  .navbar-toggler-pro {
    border-radius: 14px !important;
    border: 1px solid rgba(255, 255, 255, .14) !important;
    background: rgba(255, 255, 255, .06) !important;
    color: rgba(255, 255, 255, .9) !important;
    padding: .45rem .7rem !important;
    box-shadow: none !important;
  }

  .navbar-toggler-pro:focus {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, .18) !important;
  }

  .user-chip {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .65rem .9rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .12);
    color: rgba(255, 255, 255, .86);
    font-weight: 800;
    user-select: none;
  }

  .user-chip i {
    color: rgba(255, 255, 255, .85);
    font-size: 1.1rem;
  }

  /* ==========================================================
     BOTÓN LOGOUT PRO (integrado navbar)
     ========================================================== */
  .logout-pro {
    border-radius: 999px !important;
    padding: 12px 18px !important;

    background: rgba(255, 255, 255, .06) !important;
    border: 1px solid rgba(255, 255, 255, .14) !important;

    color: rgba(255, 255, 255, .90) !important;
    font-weight: 900;
    letter-spacing: .01em;

    transition: all .15s ease;
    backdrop-filter: blur(10px);
  }

  .logout-pro:hover {
    background: rgba(239, 68, 68, .14) !important;
    border-color: rgba(239, 68, 68, .55) !important;

    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 12px 26px rgba(0, 0, 0, .25);
  }

  .logout-pro:active {
    transform: translateY(0px);
    box-shadow: none;
  }

  .logout-pro i {
    color: rgba(239, 68, 68, .90);
  }

  .logout-pro:hover i {
    color: #fff;
  }

  /* Compact en móvil: evita navbar gigante */
  @media (max-width: 576px) {
    .brand-icon {
      width: 40px;
      height: 40px;
    }

    .navlink-pro {
      padding: .6rem .85rem !important;
    }

    .logout-pro {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<nav class="navbar navbar-expand-lg navbar-pro sticky-top">
  <div class="container-fluid px-3 px-md-4">

    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center gap-2 brand-pro" href="#">
      <span class="brand-icon">
        <i class="bi bi-p-square-fill"></i>
      </span>
      <span class="fw-extrabold brand-text">Estacionamiento</span>
      <span class="chip-mini d-none d-sm-inline-flex">
        <i class="bi bi-lightning-charge-fill text-warning"></i>
        <span>PRO</span>
      </span>
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler navbar-toggler-pro" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPro"
      aria-controls="navbarPro" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list fs-3"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarPro">

      <!-- Left links (opcional: agrega/quita sin romper) -->
      <ul class="navbar-nav me-auto mt-3 mt-lg-0 gap-2 gap-lg-3 align-items-lg-center">

        <!-- Principal -->
        <li class="nav-item">
          <a class="nav-link navlink-pro" href="<?php echo URL_BASE; ?>vistas/dashboard.php">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
          </a>
        </li>

        <!-- Operación (agrupa para que no se amontone) -->
        <li class="nav-item dropdown">
          <a class="nav-link navlink-pro dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-arrow-left-right me-2"></i>Operación
          </a>
          <ul class="dropdown-menu dropdown-menu-pro">
            <li>
              <a class="dropdown-item dropdown-item-pro" href="<?php echo URL_BASE; ?>vistas/entrada.php">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entradas
              </a>
            </li>
            <li>
              <a class="dropdown-item dropdown-item-pro" href="<?php echo URL_BASE; ?>vistas/salidas.php">
                <i class="bi bi-box-arrow-left me-2"></i>Salidas
              </a>
            </li>
            <li>
              <a class="dropdown-item dropdown-item-pro" href="<?php echo URL_BASE; ?>vistas/pensiones.php">
                <i class="bi bi-calendar2-check me-2"></i>Pensiones
              </a>
            </li>
          </ul>
        </li>

        <!-- Administración -->
        <li class="nav-item dropdown">
          <a class="nav-link navlink-pro dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-sliders2-vertical me-2"></i>Administración
          </a>
          <ul class="dropdown-menu dropdown-menu-pro">
            <li>
              <a class="dropdown-item dropdown-item-pro" href="<?php echo URL_BASE; ?>vistas/usuarios.php">
                <i class="bi bi-people me-2"></i>Usuarios
              </a>
            </li>
            <li>
              <a class="dropdown-item dropdown-item-pro" href="<?php echo URL_BASE; ?>vistas/configuracion.php">
                <i class="bi bi-gear me-2"></i>Configuración
              </a>
            </li>
            <li>
              <a class="dropdown-item dropdown-item-pro" href="<?php echo URL_BASE; ?>vistas/reportes.php">
                <i class="bi bi-bar-chart-line me-2"></i>Reportes
              </a>
            </li>
          </ul>
        </li>

      </ul>

      <!-- Right actions -->
      <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">

        <!-- (Opcional) chip de usuario si tienes sesión -->
        <?php if (!empty($_SESSION['usuario'])): ?>
          <div class="user-chip d-none d-md-inline-flex">
            <i class="bi bi-person-circle"></i>
            <span class="fw-semibold"><?php echo htmlspecialchars((string)$_SESSION['usuario']); ?></span>
          </div>
        <?php endif; ?>

        <!-- Logout -->
        <form method="POST"
          action="<?php echo URL_BASE; ?>/app/controladores/LoginControlador.php?accion=logout"
          class="m-0">

          <button type="submit" class="btn btn-lg d-inline-flex align-items-center gap-2 logout-pro">
            <i class="bi bi-box-arrow-right fs-5"></i>
            <span class="fw-extrabold">Cerrar sesión</span>
          </button>

        </form>

      </div>
    </div>
  </div>
</nav>