<?php
// vistas/login/index.php
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root {
      --radius: 20px;
      --bg1: #0b1220;
      --bg2: #0f1b32;
      --glass: rgba(255, 255, 255, .10);
      --glass2: rgba(255, 255, 255, .16);
      --border: rgba(255, 255, 255, .18);
      --textMuted: rgba(255, 255, 255, .72);
      --shadow: 0 12px 40px rgba(0, 0, 0, .35);
    }

    body {
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      min-height: 100vh;
      margin: 0;
      background:
        radial-gradient(1200px 700px at 10% 10%, rgba(59, 130, 246, .22), transparent 55%),
        radial-gradient(900px 600px at 90% 15%, rgba(34, 197, 94, .14), transparent 60%),
        radial-gradient(900px 700px at 70% 90%, rgba(168, 85, 247, .18), transparent 60%),
        linear-gradient(160deg, var(--bg1), var(--bg2));
      color: #fff;
    }

    .center-wrap {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 1.25rem;
    }

    .login-shell {
      width: min(520px, 100%);
    }

    .brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .85rem;
      margin-bottom: 1rem;
      user-select: none;
    }

    .brand .logo {
      width: 54px;
      height: 54px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      background: rgba(255, 255, 255, .10);
      border: 1px solid rgba(255, 255, 255, .18);
      box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
    }

    .brand .title {
      text-align: left;
      line-height: 1.1;
    }

    .brand .title .h4 {
      margin: 0;
      font-weight: 700;
      letter-spacing: .2px;
    }

    .brand .title .sub {
      color: var(--textMuted);
      font-size: .95rem;
      margin-top: .15rem;
    }

    .card-glass {
      border-radius: var(--radius);
      background: linear-gradient(180deg, rgba(255, 255, 255, .13), rgba(255, 255, 255, .07));
      border: 1px solid rgba(255, 255, 255, .18);
      box-shadow: var(--shadow);
      overflow: hidden;
      position: relative;
    }

    .card-glass:before {
      content: "";
      position: absolute;
      inset: 0;
      background:
        radial-gradient(700px 220px at 30% 0%, rgba(255, 255, 255, .12), transparent 55%),
        radial-gradient(450px 250px at 80% 20%, rgba(255, 255, 255, .10), transparent 60%);
      pointer-events: none;
    }

    .card-body {
      position: relative;
      z-index: 1;
    }

    .badge-soft {
      background: rgba(255, 255, 255, .12) !important;
      border: 1px solid rgba(255, 255, 255, .18);
      color: #fff !important;
    }

    .help {
      color: var(--textMuted);
      font-size: .98rem;
    }

    .form-label {
      color: rgba(255, 255, 255, .90);
    }

    .form-control,
    .form-select {
      border-radius: 16px !important;
      background: rgba(255, 255, 255, .10) !important;
      border: 1px solid rgba(255, 255, 255, .18) !important;
      color: #fff !important;
      box-shadow: none !important;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, .55);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: rgba(255, 255, 255, .35) !important;
      outline: 0 !important;
      box-shadow: 0 0 0 .25rem rgba(59, 130, 246, .22) !important;
    }

    .input-group .btn {
      border-radius: 16px !important;
      border: 1px solid rgba(255, 255, 255, .18) !important;
      background: rgba(255, 255, 255, .10) !important;
      color: #fff !important;
    }

    .btn {
      border-radius: 16px !important;
      box-shadow: 0 10px 24px rgba(0, 0, 0, .18);
    }

    .btn-primary {
      background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
      border: 0 !important;
    }

    .btn-success {
      background: linear-gradient(135deg, #22c55e, #16a34a) !important;
      border: 0 !important;
    }

    .btn-outline-light {
      border-color: rgba(255, 255, 255, .22) !important;
      color: #fff !important;
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, .12) !important;
    }

    .divider {
      height: 1px;
      background: rgba(255, 255, 255, .16);
      margin: 1.1rem 0;
    }

    .sticky-actions {
      position: sticky;
      bottom: 0;
      z-index: 1020;
      background: rgba(10, 18, 32, .65);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, .14);
      border-radius: 18px;
      padding: .75rem;
      margin-top: .9rem;
      display: none;
      box-shadow: 0 14px 40px rgba(0, 0, 0, .30);
    }

    .sticky-actions.show {
      display: block;
    }

    .alert {
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, .14);
    }

    .foot {
      text-align: center;
      color: rgba(255, 255, 255, .60);
      font-size: .85rem;
      margin-top: .85rem;
    }

    @media (max-width: 420px) {
      .brand .title {
        text-align: center;
      }

      .brand {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <div class="center-wrap">
    <div class="login-shell">

      <div class="brand">
        <div class="logo">
          <i class="bi bi-car-front-fill fs-4"></i>
        </div>
        <div class="title">
          <div class="h4">Acceso al sistema</div>
          <div class="sub">Estacionamiento • Operación y Caja</div>
        </div>
      </div>

      <div class="card-glass">
        <div class="card-body p-3 p-md-4">

          <div class="d-flex align-items-center justify-content-between mb-2">
            <h1 class="h5 mb-0">
              <i class="bi bi-shield-lock me-2"></i>Iniciar sesión
            </h1>
            <span class="badge badge-soft">
              <i class="bi bi-tablet me-1"></i>Tablet-first
            </span>
          </div>

          <div class="help mb-3">
            <i class="bi bi-info-circle me-1"></i>
            Ingresa tu <b>usuario</b> y <b>contraseña</b>.
          </div>

          <form id="formLogin" autocomplete="off">
            <div class="mb-3">
              <label class="form-label fw-semibold">
                <i class="bi bi-person-badge me-1"></i>Usuario
              </label>
              <input id="txtUsuario" type="text" class="form-control form-control-lg" placeholder="Ej. cajero1" maxlength="50" required>
            </div>

            <div class="mb-2">
              <label class="form-label fw-semibold">
                <i class="bi bi-key me-1"></i>Contraseña
              </label>
              <div class="input-group input-group-lg">
                <input id="txtPassword" type="password" class="form-control" placeholder="••••••••" maxlength="128" required>
                <button id="btnTogglePass" type="button" class="btn btn-outline-light">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-3">
              <div class="form-check">
                <input id="chkRecordar" class="form-check-input" type="checkbox">
                <label class="form-check-label">
                  <i class="bi bi-check2-square me-1"></i>Recordarme
                </label>
              </div>

              <button id="btnDemo" type="button" class="btn btn-outline-light btn-lg">
                <i class="bi bi-magic me-2"></i>Demo
              </button>
            </div>

            <div id="alerta" class="alert alert-danger mt-3 d-none" role="alert"></div>

            <div class="divider"></div>

            <button id="btnEntrarInline" type="submit" class="btn btn-primary btn-lg w-100">
              <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
            </button>

            <div class="help mt-2">
              <i class="bi bi-lightning-charge me-1"></i>
              La barra de acciones aparece solo si cambias algo.
            </div>
          </form>
        </div>
      </div>

      <div class="sticky-actions" id="stickyActions">
        <div class="d-flex gap-2">
          <button id="btnEntrarSticky" class="btn btn-success btn-lg flex-fill">
            <i class="bi bi-check2-circle me-2"></i>Entrar
          </button>
          <button id="btnCancelar" class="btn btn-outline-light btn-lg flex-fill">
            <i class="bi bi-x-circle me-2"></i>Cancelar
          </button>
        </div>
      </div>

      <div class="foot">
        <i class="bi bi-lock me-1"></i>
        Seguridad: sesión + roles (ADMIN/CAJERO/OPERADOR)
      </div>

    </div>
  </div>

  <script>
    window.URL_BASE = "<?php echo defined('URL_BASE') ? URL_BASE : 'http://localhost/sistema_estacionamiento/'; ?>";
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../publico/js/modulos/login.js"></script>
</body>

</html>