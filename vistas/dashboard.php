<?php require_once '../config/configuracion.php'; ?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --radius: 20px;
        }

        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: #f7f8fa;
        }

        .card-ui {
            border-radius: var(--radius);
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
            border: 1px solid rgba(0, 0, 0, .06);
            background: #fff;
        }

        .btn-lg,
        .form-control-lg,
        .form-select-lg {
            border-radius: 14px;
        }

        .chip {
            border-radius: 999px;
            padding: .35rem .65rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .sticky-actions {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding: .75rem;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(6px);
            border-top: 1px solid rgba(0, 0, 0, .08);
            box-shadow: 0 -0.125rem .25rem rgba(0, 0, 0, .06);
            display: none;
            z-index: 1040;
        }

        .content-pad {
            padding-bottom: 90px;
        }

        .touch-link {
            text-decoration: none;
        }

        .touch-tile {
            border-radius: 18px;
            border: 1px solid rgba(0, 0, 0, .06);
            background: #fff;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
            padding: 14px 14px;
            min-height: 86px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .touch-tile .title {
            font-weight: 800;
            margin: 0;
        }

        .touch-tile .sub {
            margin: 0;
            color: #6c757d;
            font-weight: 600;
        }

        .mono {
            font-variant-numeric: tabular-nums;
        }

        .hint {
            color: #6c757d;
            font-weight: 600;
        }

        .divider-soft {
            height: 1px;
            background: rgba(0, 0, 0, .06);
            margin: 12px 0;
        }

        /* ✅ KPI layout: menos amontonado */
        .kpi-card {
            padding: 16px;
        }

        .kpi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .kpi-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #495057;
            font-weight: 800;
            line-height: 1.15;
            min-width: 0;
        }

        .kpi-title .bi {
            font-size: 1.05rem;
            opacity: .95;
        }

        .kpi-title span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kpi-value {
            margin-top: 10px;
            font-size: 1.85rem;
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -.02em;
        }

        .kpi-meta {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: #6c757d;
            font-weight: 700;
        }

        .kpi-row {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .kpi-row .bi {
            font-size: 1rem;
        }

        .kpi-row .txt {
            display: flex;
            align-items: baseline;
            gap: 6px;
            min-width: 0;
            flex-wrap: wrap;
            line-height: 1.2;
        }

        .kpi-row .txt strong {
            font-weight: 900;
        }

        .kpi-chip {
            background: #f1f3f5;
            color: #111;
            border: 1px solid rgba(0, 0, 0, .06);
        }

        @media (max-width: 420px) {
            .kpi-card {
                padding: 14px;
            }

            .kpi-value {
                font-size: 1.65rem;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid p-3 p-md-4 content-pad">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="m-0 fw-bold"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h3>
                <div class="hint mt-1"><i class="bi bi-tablet me-1"></i>Tablet-first · Accesos rápidos + métricas</div>
            </div>
            <div class="d-flex gap-2">
                <button id="btnRefrescar" class="btn btn-outline-dark btn-lg">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refrescar
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-car-front-fill"></i>
                            <span>En estacionamiento</span>
                        </div>
                        <span id="chipEst" class="chip kpi-chip"><i class="bi bi-activity me-1"></i>Flujo</span>
                    </div>

                    <div id="kpiEnEst" class="kpi-value mono">0</div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-clock"></i>
                            <div class="txt">
                                <span>Promedio estancia:</span>
                                <strong><span id="kpiPromEst" class="mono">0</span> min</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-cash-coin"></i>
                            <span>Ingresos hoy</span>
                        </div>
                        <span class="chip kpi-chip"><i class="bi bi-calendar2-day me-1"></i>Hoy</span>
                    </div>

                    <div id="kpiIngresosHoy" class="kpi-value mono">$0.00</div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-receipt"></i>
                            <div class="txt">
                                <span>Salidas hoy:</span>
                                <strong><span id="kpiSalidasHoy" class="mono">0</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-person-badge"></i>
                            <span>Pensiones activas</span>
                        </div>
                        <span class="chip kpi-chip"><i class="bi bi-shield-check me-1"></i>Activas</span>
                    </div>

                    <div id="kpiPensionesAct" class="kpi-value mono">0</div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-exclamation-triangle"></i>
                            <div class="txt">
                                <span>Por vencer (7 días):</span>
                                <strong><span id="kpiPensionesVencen" class="mono">0</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-people-fill"></i>
                            <span>Usuarios activos</span>
                        </div>
                        <span class="chip kpi-chip"><i class="bi bi-person-check me-1"></i>OK</span>
                    </div>

                    <div id="kpiUsuariosAct" class="kpi-value mono">0</div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-person-gear"></i>
                            <div class="txt">
                                <span>Admins:</span>
                                <strong><span id="kpiAdmins" class="mono">0</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Accesos -->
            <div class="col-12 col-xl-7">
                <div class="card-ui p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="m-0 fw-bold"><i class="bi bi-grid-1x2-fill me-2"></i>Navegación</h5>
                        <div class="hint"><i class="bi bi-hand-index-thumb me-1"></i>Toque para abrir</div>
                    </div>

                    <div class="divider-soft"></div>

                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <a href="entrada.php" class="touch-link" data-href="ingresos">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-box-arrow-in-right me-2"></i>Ingresos</p>
                                        <p class="sub">Registrar entrada · búsqueda por placa</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="salidas.php" class="touch-link" data-href="salidas">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-box-arrow-right me-2"></i>Salidas</p>
                                        <p class="sub">Cobro · descuentos · ticket</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="pensiones.php" class="touch-link" data-href="pensiones">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-credit-card-2-front me-2"></i>Pensiones</p>
                                        <p class="sub">Altas · vigencias · pagos</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="configuracion.php" class="touch-link" data-href="tarifas">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-tags-fill me-2"></i>Tarifas</p>
                                        <p class="sub">Costo hora · boleto perdido · noche</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="usuarios.php" class="touch-link" data-href="usuarios">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-people me-2"></i>Usuarios</p>
                                        <p class="sub">Roles · activación · accesos</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="configuracion.php" class="touch-link" data-href="configuracion">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-gear-fill me-2"></i>Configuración</p>
                                        <p class="sub">Negocio · impresora · ticket</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="configuracion.php" class="touch-link" data-href="horarios">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-clock-history me-2"></i>Horarios</p>
                                        <p class="sub">Apertura · cierre · días</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="reportes.php" class="touch-link" data-href="reportes">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-graph-up-arrow me-2"></i>Reportes</p>
                                        <p class="sub">Ingresos · salidas · pensiones</p>
                                    </div>
                                    <i class="bi bi-chevron-right fs-3"></i>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="divider-soft"></div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="chip kpi-chip"><i class="bi bi-wifi me-1"></i>API: <span id="lblApiEstado">—</span></span>
                        <span class="chip kpi-chip"><i class="bi bi-database me-1"></i>Datos: <span id="lblDatosEstado">—</span></span>
                    </div>
                </div>
            </div>

            <!-- Ajustes rápidos -->
            <div class="col-12 col-xl-5">
                <div class="card-ui p-3">
                    <h5 class="m-0 fw-bold"><i class="bi bi-sliders me-2"></i>Ajustes rápidos</h5>
                    <div class="hint mt-1"><i class="bi bi-pin-angle me-1"></i>Modifica y guarda (barra inferior aparece si hay cambios)</div>

                    <div class="divider-soft"></div>

                    <form id="frmAjustes" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bi bi-shop me-1"></i>Nombre del negocio</label>
                            <input type="text" class="form-control form-control-lg" name="nombre_negocio" placeholder="Mi Estacionamiento" value="">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bi bi-currency-dollar me-1"></i>Símbolo moneda</label>
                            <input type="text" class="form-control form-control-lg" name="moneda_simbolo" maxlength="5" placeholder="$" value="$">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bi bi-printer-fill me-1"></i>Impresora</label>
                            <input type="text" class="form-control form-control-lg" name="nombre_impresora" placeholder="POS-80" value="POS-80">
                        </div>
                    </form>

                    <div class="mt-3">
                        <div class="alert alert-light border card-ui mb-0">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div>
                                    <div class="fw-bold">Tip</div>
                                    <div class="hint">La barra inferior aparece solo cuando hay cambios en el formulario.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- Sticky Actions -->
    <div id="stickyActions" class="sticky-actions">
        <div class="container-fluid d-flex gap-2">
            <button id="btnCancelar" class="btn btn-outline-secondary btn-lg w-50">
                <i class="bi bi-x-circle me-1"></i>Cancelar
            </button>
            <button id="btnGuardar" class="btn btn-dark btn-lg w-50">
                <i class="bi bi-save2 me-1"></i>Guardar
            </button>
        </div>
    </div>

    <script>
        window.URL_BASE = "<?php echo defined('URL_BASE') ? URL_BASE : 'http://localhost/sistema_estacionamiento/'; ?>";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="../publico/js/modulos/dashboard.js"></script>
</body>

</html>