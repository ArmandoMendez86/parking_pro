<?php
require_once '../config/configuracion.php';
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reportes</title>

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

      /* 🌙 Fondo más suave, menos intenso */
      background:
        radial-gradient(1200px 700px at 10% 10%, rgba(59, 130, 246, .10), transparent 60%),
        radial-gradient(900px 600px at 90% 15%, rgba(197, 29, 6, 0.07), transparent 65%),
        radial-gradient(900px 700px at 70% 90%, rgba(168, 85, 247, .08), transparent 65%),
        linear-gradient(160deg, #101a30, #131f3a);

      color: #fff;
    }


    .app-wrap {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      padding: 1.25rem;
    }

    .app-shell {
      width: min(1200px, 100%);
    }

    .brand {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
      user-select: none;
    }

    .brand-left {
      display: flex;
      align-items: center;
      gap: .85rem;
      min-width: 0;
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
      flex: 0 0 auto;
    }

    .brand .title {
      line-height: 1.1;
      min-width: 0;
    }

    .brand .title .h4 {
      margin: 0;
      font-weight: 700;
      letter-spacing: .2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .brand .title .sub {
      color: var(--textMuted);
      font-size: .95rem;
      margin-top: .15rem;
    }

    .chip {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .45rem .7rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, .10);
      border: 1px solid rgba(255, 255, 255, .18);
      color: #fff;
      white-space: nowrap;
    }

    .card-glass {
      border-radius: var(--radius);

      /* 🌫️ Glass más suave y elegante */
      background: linear-gradient(180deg,
          rgba(255, 255, 255, .09),
          rgba(255, 255, 255, .04));

      border: 1px solid rgba(255, 255, 255, .12);

      /* sombra más ligera */
      box-shadow: 0 10px 30px rgba(0, 0, 0, .22);

      overflow: hidden;
      position: relative;
    }


    .card-glass:before {
      content: "";
      position: absolute;
      inset: 0;

      /* ✨ Brillo más tenue */
      background:
        radial-gradient(700px 220px at 30% 0%, rgba(255, 255, 255, .07), transparent 65%),
        radial-gradient(450px 250px at 80% 20%, rgba(255, 255, 255, .05), transparent 70%);

      pointer-events: none;
    }


    .card-glass>* {
      position: relative;
      z-index: 1;
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
      background: rgba(255, 255, 255, .06) !important;
      
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

    .btn-dark {
      background: rgba(255, 255, 255, .12) !important;
      border: 1px solid rgba(255, 255, 255, .18) !important;
      color: #fff !important;
    }

    .btn-outline-secondary,
    .btn-outline-primary,
    .btn-outline-danger {
      border-color: rgba(255, 255, 255, .22) !important;
      color: #fff !important;
      background: rgba(255, 255, 255, .06) !important;
    }

    .btn-outline-secondary:hover,
    .btn-outline-primary:hover,
    .btn-outline-danger:hover {
      background: rgba(255, 255, 255, .12) !important;
    }

    .divider {
      height: 1px;
      background: rgba(255, 255, 255, .16);
      margin: 1rem 0;
    }

    .segmented .btn {
      border-radius: 16px !important;
    }

    .kpi {
      border-radius: 18px;
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(255, 255, 255, .14);
      padding: .85rem;
    }

    .kpi .label {
      color: var(--textMuted);
      font-size: .92rem;
      margin-bottom: .2rem;
      display: flex;
      align-items: center;
      gap: .4rem;
    }

    .kpi .value {
      font-weight: 700;
      font-size: 1.15rem;
      letter-spacing: .2px;
    }

    .mono {
      font-variant-numeric: tabular-nums;
    }

    .table {
      color: rgba(255, 255, 255, .92);
      margin: 0;
    }

    .table thead th {
      color: rgba(255, 255, 255, .68);
      border-bottom: 1px solid rgba(255, 255, 255, .14);
      position: sticky;
      top: 0;
      background: rgba(10, 18, 32, .65);
      backdrop-filter: blur(10px);
      z-index: 2;
    }

    .table td,
    .table th {
      border-color: rgba(255, 255, 255, .10);
    }

    .table tbody tr:hover {
      background: rgba(255, 255, 255, .05);
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

    @media (max-width: 992px) {
      .brand {
        flex-direction: column;
        align-items: stretch;
      }

      .brand-right {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
      }
    }
  </style>

  <script>
    window.URL_BASE = "<?php echo defined('URL_BASE') ? URL_BASE : 'http://localhost/sistema_estacionamiento/'; ?>";
  </script>
</head>

<body>
  <div class="app-wrap">
    <div class="app-shell">

      <!-- HEADER -->
      <div class="brand">
        <div class="brand-left">
          <div class="logo">
            <i class="bi bi-bar-chart-line fs-4"></i>
          </div>
          <div class="title">
            <div class="h4">Reportes</div>
            <div class="sub">Cortes por cajero • Turnos • Métodos de pago • Operación</div>
          </div>
        </div>

        <div class="brand-right d-flex align-items-center gap-2">
          <span class="chip">
            <i class="bi bi-person-badge"></i>
            <?php echo htmlspecialchars(AuthHelper::nombre() ?? ''); ?>
            <span style="opacity:.75;">(<?php echo htmlspecialchars(AuthHelper::rol() ?? ''); ?>)</span>
          </span>

          <?php include __DIR__ . "/../app/componentes/BotonLogout.php"; ?>

          <button id="btnNuevo" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-lg me-2"></i>Nuevo
          </button>
        </div>
      </div>

      <div class="row g-3">
        <!-- FILTROS -->
        <div class="col-12 col-lg-4">
          <div class="card-glass">
            <div class="p-3 p-md-4">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="h5 mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h2>
                <span class="chip">
                  <i class="bi bi-sliders"></i>
                  Ajustes
                </span>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-file-earmark-bar-graph me-1"></i>Tipo de reporte
                </label>
                <select id="filtroTipoReporte" class="form-select form-select-lg">
                  <optgroup label="Caja">
                    <option value="corte_cajero">Corte por cajero (resumen + detalle)</option>
                    <option value="corte_diario">Corte diario general</option>
                    <option value="descuentos">Auditoría de descuentos</option>
                    <option value="boletos_perdidos">Boletos perdidos</option>
                    <option value="extra_noche">Extra noche</option>
                    <option value="anticipos">Pagos adelantados (anticipos)</option>
                  </optgroup>
                  <optgroup label="Operación">
                    <option value="ocupacion">Ocupación actual</option>
                    <option value="entradas_periodo">Entradas por periodo</option>
                    <option value="estancia_promedio">Estancia promedio</option>
                  </optgroup>
                  <optgroup label="Pensiones">
                    <option value="pensiones_activas">Pensiones activas</option>
                    <option value="pensiones_vencer">Pensiones por vencer</option>
                    <option value="pagos_pensiones">Pagos de pensiones</option>
                  </optgroup>
                </select>
                <div class="help mt-1">
                  <i class="bi bi-lightning-charge me-1"></i>
                  Para “Corte por cajero”, puedes filtrar por <b>turno</b> o <b>rango</b>.
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-sliders me-1"></i>Modo de periodo
                </label>
                <div class="segmented btn-group w-100" role="group">
                  <button id="btnModoRango" type="button" class="btn btn-outline-secondary btn-lg">Rango</button>
                  <button id="btnModoTurno" type="button" class="btn btn-outline-secondary btn-lg">Turno</button>
                </div>
              </div>

              <!-- RANGO (FECHAS) -->
              <div id="bloqueRango" class="row g-3">
                <div class="col-12 col-md-6 col-lg-12">
                  <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-event me-1"></i>Desde (fecha)
                  </label>
                  <input id="filtroDesdeFecha" type="date" class="form-control form-control-lg">
                </div>
                <div class="col-12 col-md-6 col-lg-12">
                  <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-check me-1"></i>Hasta (fecha)
                  </label>
                  <input id="filtroHastaFecha" type="date" class="form-control form-control-lg">
                </div>

                <div class="col-12 col-md-6 col-lg-12">
                  <label class="form-label fw-semibold">
                    <i class="bi bi-clock me-1"></i>Desde (hora)
                  </label>
                  <input id="filtroDesdeHora" type="time" class="form-control form-control-lg" value="00:00">
                </div>
                <div class="col-12 col-md-6 col-lg-12">
                  <label class="form-label fw-semibold">
                    <i class="bi bi-clock-history me-1"></i>Hasta (hora)
                  </label>
                  <input id="filtroHastaHora" type="time" class="form-control form-control-lg" value="23:59">
                </div>
              </div>

              <!-- TURNO -->
              <div id="bloqueTurno" class="d-none">
                <div class="mb-3">
                  <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-day me-1"></i>Día
                  </label>
                  <input id="filtroTurnoFecha" type="date" class="form-control form-control-lg">
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">
                    <i class="bi bi-alarm me-1"></i>Turno
                  </label>
                  <select id="filtroTurnoPreset" class="form-select form-select-lg">
                    <option value="mañana">Mañana (08:00 - 16:00)</option>
                    <option value="tarde">Tarde (16:00 - 00:00)</option>
                    <option value="noche">Noche (00:00 - 08:00)</option>
                    <option value="custom">Personalizado</option>
                  </select>
                  <div class="help mt-1">
                    <i class="bi bi-info-circle me-1"></i>
                    Si eliges “Personalizado”, define horas abajo.
                  </div>
                </div>

                <div id="bloqueTurnoCustom" class="row g-3 d-none">
                  <div class="col-12 col-md-6 col-lg-12">
                    <label class="form-label fw-semibold">
                      <i class="bi bi-clock me-1"></i>Hora inicio
                    </label>
                    <input id="filtroTurnoHoraInicio" type="time" class="form-control form-control-lg" value="08:00">
                  </div>
                  <div class="col-12 col-md-6 col-lg-12">
                    <label class="form-label fw-semibold">
                      <i class="bi bi-clock-history me-1"></i>Hora fin
                    </label>
                    <input id="filtroTurnoHoraFin" type="time" class="form-control form-control-lg" value="16:00">
                  </div>
                </div>
              </div>

              <div class="divider"></div>

              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-person-badge me-1"></i>Cajero / Usuario
                </label>
                <select id="filtroUsuario" class="form-select form-select-lg">
                  <option value="">Todos</option>
                </select>
                <div class="help mt-1">
                  <i class="bi bi-shield-check me-1"></i>Para corte por cajero, este filtro es clave.
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-credit-card-2-front me-1"></i>Método de pago
                </label>
                <select id="filtroMetodoPago" class="form-select form-select-lg">
                  <option value="">Todos</option>
                  <option value="Efectivo">Efectivo</option>
                  <option value="Tarjeta">Tarjeta</option>
                  <option value="Transferencia">Transferencia</option>
                  <option value="Otro">Otro</option>
                </select>
                <div class="help mt-1">
                  <i class="bi bi-info-circle me-1"></i>
                  Aplica principalmente a salidas (cortes).
                </div>
              </div>

              <div class="mt-3 d-grid gap-2 d-md-flex">
                <button id="btnAplicar" class="btn btn-dark btn-lg flex-fill">
                  <i class="bi bi-search me-2"></i>Aplicar
                </button>
                <button id="btnLimpiar" class="btn btn-outline-secondary btn-lg flex-fill">
                  <i class="bi bi-eraser me-2"></i>Limpiar
                </button>
              </div>

              <div class="divider"></div>

              <div class="d-grid gap-2">
                <button id="btnExportarCsv" class="btn btn-outline-primary btn-lg">
                  <i class="bi bi-filetype-csv me-2"></i>Exportar CSV
                </button>
                <button id="btnExportarPdf" class="btn btn-outline-danger btn-lg">
                  <i class="bi bi-filetype-pdf me-2"></i>Exportar PDF
                </button>
              </div>

              <div class="help mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Tip: Usa <b>Turno</b> para cortes rápidos, y <b>Rango</b> para auditoría.
              </div>
            </div>
          </div>
        </div>

        <!-- RESULTADOS -->
        <div class="col-12 col-lg-8">
          <div class="card-glass">
            <div class="p-3 p-md-4 border-bottom" style="border-color: rgba(255,255,255,.12) !important;">
              <div class="d-flex align-items-center justify-content-between">
                <h2 class="h5 mb-0">
                  <i class="bi bi-list-check me-2"></i>Resultados
                </h2>
                <span id="badgeTotal" class="chip">
                  <i class="bi bi-collection me-1"></i>0
                </span>
              </div>
              <div class="help mt-2">
                <i class="bi bi-arrow-repeat me-1"></i>
                Cambia filtros y presiona <b>Aplicar</b>.
              </div>
            </div>

            <!-- KPIs -->
            <div class="p-3 p-md-4 border-bottom" style="border-color: rgba(255,255,255,.12) !important;">
              <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="kpi">
                    <div class="label"><i class="bi bi-cash-coin"></i>Total vendido</div>
                    <div id="kpiTotalVendido" class="value mono">$0.00</div>
                  </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="kpi">
                    <div class="label"><i class="bi bi-receipt"></i>Total recibido</div>
                    <div id="kpiTotalRecibido" class="value mono">$0.00</div>
                  </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="kpi">
                    <div class="label"><i class="bi bi-arrow-return-left"></i>Total cambio</div>
                    <div id="kpiTotalCambio" class="value mono">$0.00</div>
                  </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                  <div class="kpi">
                    <div class="label"><i class="bi bi-tag"></i>Descuentos</div>
                    <div id="kpiTotalDescuentos" class="value mono">$0.00</div>
                  </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="kpi">
                    <div class="label"><i class="bi bi-moon-stars"></i>Extra noche</div>
                    <div id="kpiTotalExtraNoche" class="value mono">$0.00</div>
                  </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="kpi">
                    <div class="label"><i class="bi bi-ticket-perforated"></i>Boletos perdidos</div>
                    <div id="kpiBoletosPerdidos" class="value mono">0</div>
                  </div>
                </div>
              </div>

              <div class="mt-3">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="fw-semibold">
                    <i class="bi bi-credit-card me-1"></i>Desglose por método de pago
                  </div>
                  <span class="help">Efectivo / Tarjeta / Transferencia / Otro</span>
                </div>

                <div class="row g-2 mt-2">
                  <div class="col-6 col-md-3">
                    <div class="kpi">
                      <div class="label">Efectivo</div>
                      <div id="kpiEfectivo" class="value mono">$0.00</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="kpi">
                      <div class="label">Tarjeta</div>
                      <div id="kpiTarjeta" class="value mono">$0.00</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="kpi">
                      <div class="label">Transferencia</div>
                      <div id="kpiTransferencia" class="value mono">$0.00</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="kpi">
                      <div class="label">Otro</div>
                      <div id="kpiOtro" class="value mono">$0.00</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TABLA -->
            <div class="p-3 p-md-4">
              <div class="table-responsive" style="max-height: 55vh; border-radius: 18px; overflow: hidden; border: 1px solid rgba(255,255,255,.12);">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Placa</th>
                      <th>Cajero</th>
                      <th>Método</th>
                      <th class="text-end">Total</th>
                      <th class="text-end">Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyReportes">
                    <!-- JS -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Plantilla -->
          <div class="card-glass mt-3">
            <div class="p-3 p-md-4">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="h5 mb-0">
                  <i class="bi bi-ui-checks-grid me-2"></i>Plantilla (opcional)
                </h2>
                <span class="chip">
                  <i class="bi bi-chat-left-text"></i>
                  Notas
                </span>
              </div>

              <div class="help mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Notas internas del reporte (no afecta cálculos).
              </div>

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label fw-semibold">
                    <i class="bi bi-chat-left-text me-1"></i>Notas internas del reporte
                  </label>
                  <textarea id="txtNotas" class="form-control form-control-lg" rows="3" placeholder="Notas..."></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Sticky actions -->
          <div id="stickyActions" class="sticky-actions mt-3">
            <div class="d-flex gap-2">
              <button id="btnGuardar" class="btn btn-success btn-lg flex-fill">
                <i class="bi bi-check2-circle me-2"></i>Guardar
              </button>
              <button id="btnCancelar" class="btn btn-outline-secondary btn-lg flex-fill">
                <i class="bi bi-x-circle me-2"></i>Cancelar
              </button>
            </div>
          </div>

        </div>
      </div>

      <div class="text-center mt-3" style="color: rgba(255,255,255,.55); font-size: .85rem;">
        <i class="bi bi-shield-lock me-1"></i>
        Sesión activa • Interfaz tablet-first
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script type="module" src="../publico/js/modulos/reportes.js"></script>
</body>

</html>