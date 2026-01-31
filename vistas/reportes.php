<?php
// vistas/reportes/index.php
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
    :root { --radius: 20px; }
    body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: #f6f7fb; }
    .card-soft { border-radius: var(--radius); box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); border: 0; }
    .sticky-actions {
      position: sticky;
      bottom: 0;
      z-index: 1020;
      background: rgba(255,255,255,.92);
      backdrop-filter: blur(8px);
      border-top: 1px solid rgba(0,0,0,.08);
      padding: .75rem;
      display: none;
    }
    .sticky-actions.show { display: block; }
    .chip { border-radius: 999px; padding: .25rem .6rem; font-size: .85rem; }
    .table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
  </style>

  <script>
    // Debes tener URL_BASE definido globalmente en tu layout principal o config.
    // Si no existe, aquí se deja como fallback seguro.
    window.URL_BASE = window.URL_BASE || '';
  </script>
</head>

<body>
  <div class="container py-3 py-md-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="h3 mb-1">
          <i class="bi bi-bar-chart-line me-2"></i>Reportes
        </h1>
        <div class="text-muted">Listado + filtros + exportación (demo UI)</div>
      </div>

      <button id="btnNuevo" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg me-2"></i>Nuevo reporte
      </button>
    </div>

    <div class="row g-3">
      <!-- Filtros -->
      <div class="col-12 col-lg-4">
        <div class="card card-soft">
          <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center mb-2">
              <h2 class="h5 mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h2>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">
                <i class="bi bi-file-earmark-text me-1"></i>Tipo de reporte
              </label>
              <select id="filtroTipo" class="form-select form-select-lg">
                <option value="ventas">Ventas</option>
                <option value="inventario">Inventario</option>
                <option value="clientes">Clientes</option>
              </select>
            </div>

            <div class="row g-3">
              <div class="col-12 col-md-6 col-lg-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-calendar-event me-1"></i>Desde
                </label>
                <input id="filtroDesde" type="date" class="form-control form-control-lg">
              </div>
              <div class="col-12 col-md-6 col-lg-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-calendar-check me-1"></i>Hasta
                </label>
                <input id="filtroHasta" type="date" class="form-control form-control-lg">
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

            <hr class="my-4">

            <div class="d-grid gap-2">
              <button id="btnExportarCsv" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-filetype-csv me-2"></i>Exportar CSV
              </button>
              <button id="btnExportarPdf" class="btn btn-outline-danger btn-lg">
                <i class="bi bi-filetype-pdf me-2"></i>Exportar PDF
              </button>
            </div>

            <div class="mt-3 text-muted small">
              <i class="bi bi-info-circle me-1"></i>
              Esta es una vista prototipo con datos ficticios y barra sticky por cambios.
            </div>
          </div>
        </div>
      </div>

      <!-- Listado -->
      <div class="col-12 col-lg-8">
        <div class="card card-soft">
          <div class="card-body p-0">
            <div class="p-3 p-md-4 border-bottom d-flex align-items-center justify-content-between">
              <h2 class="h5 mb-0">
                <i class="bi bi-list-check me-2"></i>Resultados
              </h2>
              <span id="badgeTotal" class="chip bg-light text-dark border">
                <i class="bi bi-collection me-1"></i>0
              </span>
            </div>

            <div class="p-3 p-md-4">
              <div class="table-responsive" style="max-height: 60vh;">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr class="text-muted">
                      <th>Folio</th>
                      <th>Tipo</th>
                      <th>Rango</th>
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
        </div>
      </div>
    </div>

    <!-- Formulario -->
    <div class="card card-soft mt-3">
      <div class="card-body p-3 p-md-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h2 class="h5 mb-0">
            <i class="bi bi-ui-checks-grid me-2"></i>Formulario
          </h2>
          <span id="estadoModo" class="chip bg-light text-dark border">
            <i class="bi bi-pencil-square me-1"></i>Nuevo
          </span>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-hash me-1"></i>Folio
            </label>
            <input id="txtFolio" type="text" class="form-control form-control-lg" placeholder="AUTO" disabled>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-file-earmark-text me-1"></i>Tipo
            </label>
            <select id="txtTipo" class="form-select form-select-lg">
              <option value="ventas">Ventas</option>
              <option value="inventario">Inventario</option>
              <option value="clientes">Clientes</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-gear me-1"></i>Estado
            </label>
            <select id="txtEstado" class="form-select form-select-lg">
              <option value="borrador">Borrador</option>
              <option value="generado">Generado</option>
              <option value="enviado">Enviado</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-calendar-event me-1"></i>Desde
            </label>
            <input id="txtDesde" type="date" class="form-control form-control-lg">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-calendar-check me-1"></i>Hasta
            </label>
            <input id="txtHasta" type="date" class="form-control form-control-lg">
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">
              <i class="bi bi-chat-left-text me-1"></i>Notas
            </label>
            <textarea id="txtNotas" class="form-control form-control-lg" rows="3" placeholder="Notas del reporte..."></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Sticky actions -->
    <div id="stickyActions" class="sticky-actions rounded-top-4 mt-3">
      <div class="container px-0">
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

    <script>
        window.URL_BASE = "<?php echo defined('URL_BASE') ? URL_BASE : 'http://localhost/sistema_estacionamiento/'; ?>";
    </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="../publico/js/modulos/reportes.js"></script>
</body>
</html>
