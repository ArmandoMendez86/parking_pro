<?php require_once '../config/configuracion.php'; ?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================
           THEME OVERRIDE (SOFT GLASS / DARK MODE / SPACIOUS)
           ========================================================== */
        :root {
            --radius: 24px; /* Bordes más redondeados */
            --bg1: #101a30;
            --bg2: #131f3a;
            --glassA: rgba(255, 255, 255, .09);
            --glassB: rgba(255, 255, 255, .04);
            --border: rgba(255, 255, 255, .12);
            --textMuted: rgba(255, 255, 255, .72);
        }

        body {
            font-family: Inter, system-ui, -apple-system, sans-serif !important;
            background:
                radial-gradient(1200px 700px at 10% 10%, rgba(59, 130, 246, .10), transparent 60%),
                radial-gradient(900px 600px at 90% 15%, rgba(34, 197, 94, .07), transparent 65%),
                linear-gradient(160deg, var(--bg1), var(--bg2)) !important;
            color: #fff !important;
            padding-bottom: 140px; /* Más espacio abajo para la barra flotante */
            min-height: 100vh;
        }

        /* --- CARDS: GLASS & SPACIOUS --- */
        .card-ui {
            background: linear-gradient(180deg, var(--glassA), var(--glassB)) !important;
            border: 1px solid var(--border) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .25) !important;
            border-radius: var(--radius);
            color: #fff !important;
            height: 100%; /* Para que todas las cards de una fila tengan misma altura */
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Distribución vertical perfecta */
        }

        /* --- TYPOGRAPHY --- */
        h1, h2, h3, h4, h5, h6, .text-dark, .kpi-title { color: #fff !important; }
        .text-muted, .hint, .sub, .kpi-meta { color: var(--textMuted) !important; }
        
        .divider-soft { 
            height: 1px;
            background: rgba(255, 255, 255, .1) !important; 
            margin: 1.5rem 0; /* Más margen en los divisores */
        }

        /* --- INPUTS GIGANTES & CENTRADOS --- */
        .form-control, .form-select {
            border-radius: 16px !important;
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            color: #fff !important;
            padding: 14px 18px !important; /* Más padding interno */
            font-size: 1.1rem;
        }
        .form-control::placeholder { color: rgba(255, 255, 255, .55) !important; }
        .form-control:focus, .form-select:focus {
            border-color: rgba(255, 255, 255, .4) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .15) !important;
            background: rgba(255, 255, 255, .09) !important;
        }
        label { margin-bottom: 8px; font-weight: 500; }

        /* --- BOTONES --- */
        .btn {
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 600;
        }
        .btn-outline-secondary {
            border-color: rgba(255, 255, 255, .22) !important;
            color: #fff !important;
            background: rgba(255, 255, 255, .06) !important;
        }
        .btn-outline-secondary:hover {
            background: rgba(255, 255, 255, .15) !important;
        }
        .btn-dark {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            border: 0 !important;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        /* --- KPI (TARJETAS DE DATOS) --- */
        .chip {
            background: rgba(255, 255, 255, .1) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, .1) !important;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* Flexbox mágica para centrar contenido KPI */
        .kpi-body {
            flex-grow: 1;
            display: flex;
            align-items: center; /* Centrado vertical */
            padding: 1.5rem 0;
        }
        .kpi-value { 
            font-size: 2.5rem; 
            font-weight: 800; 
            line-height: 1;
            letter-spacing: -1px;
        }

        /* --- TOUCH TILES (NAVEGACIÓN) --- */
        .touch-link { text-decoration: none; display: block; height: 100%; }
        
        .touch-tile {
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03)) !important;
            border: 1px solid var(--border) !important;
            padding: 0 25px; /* Padding lateral */
            min-height: 110px; /* ALTURA ASEGURADA */
            
            /* ALINEACIÓN TOTAL */
            display: flex;
            align-items: center; /* Centrado vertical perfecto */
            justify-content: space-between; /* Espacio entre texto e icono */
            
            gap: 15px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .touch-tile:hover {
            transform: translateY(-4px);
            background: linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.06)) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-color: rgba(255,255,255,0.3) !important;
        }

        .touch-tile .title { font-weight: 700; margin: 0; font-size: 1.15rem; line-height: 1.2; }
        .touch-tile .sub { margin: 4px 0 0 0; font-size: 0.9rem; font-weight: 400; opacity: 0.8; }
        .touch-tile i.bi-chevron-right { opacity: 0.6; font-size: 1.4rem; }

        /* --- ALERTA --- */
        .alert-light {
            background: rgba(255,255,255,0.05) !important;
            border: 1px dashed rgba(255,255,255,0.2) !important;
            color: var(--textMuted) !important;
            border-radius: 16px;
            padding: 1rem 1.25rem;
        }
        .alert-light .fw-bold { color: #fff !important; }

        /* --- STICKY BAR --- */
        .sticky-actions {
            position: fixed; left: 0; right: 0; bottom: 0;
            padding: 1.25rem;
            background: rgba(10, 18, 32, .85) !important;
            backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255, 255, 255, .14) !important;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.4);
            display: none;
            z-index: 1040;
        }

        /* Layout helpers */
        .content-pad { padding: 30px; } /* Padding global del contenedor */
        .kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .kpi-title { font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .kpi-row { display: flex; align-items: center; gap: 10px; font-size: 0.95rem; }
    </style>
</head>

<body>
    <div class="container-fluid content-pad">

        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <h2 class="m-0 fw-bold display-6"><i class="bi bi-speedometer2 me-3"></i>Dashboard</h2>
                <div class="hint mt-2 fs-5">Resumen general y accesos rápidos</div>
            </div>
            <div class="d-flex gap-3">
                <button id="btnRefrescar" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                </button>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui p-4">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-car-front-fill fs-5"></i>
                            <span>En sitio</span>
                        </div>
                        <span id="chipEst" class="chip"><i class="bi bi-activity me-1"></i>Flujo</span>
                    </div>

                    <div class="kpi-body">
                        <div id="kpiEnEst" class="kpi-value">0</div>
                    </div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-clock"></i>
                            <span>Promedio: <strong><span id="kpiPromEst">0</span> min</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui p-4">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-cash-coin fs-5"></i>
                            <span>Ingresos</span>
                        </div>
                        <span class="chip"><i class="bi bi-calendar2-day me-1"></i>Hoy</span>
                    </div>

                    <div class="kpi-body">
                        <div id="kpiIngresosHoy" class="kpi-value">$0.00</div>
                    </div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-receipt"></i>
                            <span>Salidas hoy: <strong><span id="kpiSalidasHoy">0</span></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui p-4">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-person-badge fs-5"></i>
                            <span>Pensiones</span>
                        </div>
                        <span class="chip"><i class="bi bi-shield-check me-1"></i>Activas</span>
                    </div>

                    <div class="kpi-body">
                        <div id="kpiPensionesAct" class="kpi-value">0</div>
                    </div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Vencen (7d): <strong><span id="kpiPensionesVencen">0</span></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-ui p-4">
                    <div class="kpi-top">
                        <div class="kpi-title">
                            <i class="bi bi-people-fill fs-5"></i>
                            <span>Usuarios</span>
                        </div>
                        <span class="chip"><i class="bi bi-person-check me-1"></i>Sistema</span>
                    </div>

                    <div class="kpi-body">
                        <div id="kpiUsuariosAct" class="kpi-value">0</div>
                    </div>

                    <div class="kpi-meta">
                        <div class="kpi-row">
                            <i class="bi bi-person-gear"></i>
                            <span>Admins: <strong><span id="kpiAdmins">0</span></strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <div class="card-ui p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="m-0 fw-bold"><i class="bi bi-grid-1x2-fill me-3"></i>Navegación</h4>
                        <div class="hint"><i class="bi bi-hand-index-thumb me-1"></i>Menú Principal</div>
                    </div>

                    <div class="divider-soft"></div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <a href="entrada.php" class="touch-link" data-href="ingresos">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-box-arrow-in-right me-2 text-success"></i>Ingresos</p>
                                        <p class="sub">Registrar entrada</p>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="salidas.php" class="touch-link" data-href="salidas">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Salidas</p>
                                        <p class="sub">Cobro y ticket</p>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="pensiones.php" class="touch-link" data-href="pensiones">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-credit-card-2-front me-2 text-warning"></i>Pensiones</p>
                                        <p class="sub">Control mensual</p>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="configuracion.php" class="touch-link" data-href="tarifas">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-tags-fill me-2 text-info"></i>Tarifas</p>
                                        <p class="sub">Precios y costos</p>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="usuarios.php" class="touch-link" data-href="usuarios">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-people me-2"></i>Usuarios</p>
                                        <p class="sub">Permisos</p>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="configuracion.php" class="touch-link" data-href="configuracion">
                                <div class="touch-tile">
                                    <div>
                                        <p class="title"><i class="bi bi-gear-fill me-2"></i>Configuración</p>
                                        <p class="sub">Sistema global</p>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        </div>
                        
                         <div class="col-12">
                            <a href="reportes.php" class="touch-link" data-href="reportes">
                                <div class="touch-tile" style="background: rgba(255,255,255,0.03) !important;">
                                    <div>
                                        <p class="title"><i class="bi bi-graph-up-arrow me-2"></i>Reportes y Estadísticas</p>
                                        <p class="sub">Ver historial de ingresos</p>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="divider-soft"></div>

                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <span class="text-muted small fw-bold">ESTADO DEL SISTEMA:</span>
                        <span class="chip"><i class="bi bi-wifi me-2"></i>API: <span id="lblApiEstado" class="text-white">...</span></span>
                        <span class="chip"><i class="bi bi-database me-2"></i>Datos: <span id="lblDatosEstado" class="text-white">...</span></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card-ui p-4">
                    <div>
                        <h4 class="m-0 fw-bold"><i class="bi bi-sliders me-3"></i>Ajustes Rápidos</h4>
                        <div class="hint mt-2">Configuración básica inmediata</div>
                    </div>

                    <div class="divider-soft"></div>

                    <form id="frmAjustes" autocomplete="off" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label"><i class="bi bi-shop me-2"></i>Nombre del negocio</label>
                            <input type="text" class="form-control" name="nombre_negocio" placeholder="Ej. Estacionamiento Central" value="">
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label"><i class="bi bi-currency-dollar me-2"></i>Moneda</label>
                                <input type="text" class="form-control text-center" name="moneda_simbolo" maxlength="5" placeholder="$" value="$">
                            </div>
                            <div class="col-6">
                                <label class="form-label"><i class="bi bi-printer-fill me-2"></i>Impresora</label>
                                <input type="text" class="form-control" name="nombre_impresora" placeholder="POS-80" value="POS-80">
                            </div>
                        </div>
                    </form>
                    
                    <div class="mt-auto pt-4">
                        <div class="alert alert-light mb-0 d-flex align-items-center gap-3">
                            <i class="bi bi-info-circle-fill fs-4 text-primary"></i>
                            <div>
                                <div class="fw-bold">Nota Importante</div>
                                <div class="small opacity-75">Si realizas cambios aquí, aparecerá una barra inferior para guardar.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="stickyActions" class="sticky-actions">
        <div class="container-fluid d-flex gap-3 justify-content-center">
            <button id="btnCancelar" class="btn btn-outline-secondary btn-lg" style="min-width: 150px;">
                <i class="bi bi-x-circle me-2"></i>Cancelar
            </button>
            <button id="btnGuardar" class="btn btn-dark btn-lg px-5 shadow-lg">
                <i class="bi bi-save2 me-2"></i>GUARDAR CAMBIOS
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