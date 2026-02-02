<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salidas | Control de Cobro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================
           ADN VISUAL PRO (Dark Glass) - FIXED
           ========================================================== */
        :root {
            --radius: 24px;
            --bg1: #101a30;
            --bg2: #131f3a;
            --glassA: rgba(255, 255, 255, .09);
            --glassB: rgba(255, 255, 255, .04);
            --border: rgba(255, 255, 255, .12);
            --muted: rgba(255, 255, 255, .72);

            --bs-primary: #3b82f6;
            --bs-primary-hover: #2563eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(1200px 700px at 10% 10%, rgba(59, 130, 246, .10), transparent 60%),
                radial-gradient(900px 600px at 90% 15%, rgba(34, 197, 94, .07), transparent 65%),
                linear-gradient(160deg, var(--bg1), var(--bg2)) !important;
            background-attachment: fixed; /* Evita saltos al scrollear */
            color: #fff !important;
            padding-bottom: 140px;
            min-height: 100vh;
        }

        /* Text helpers */
        .text-secondary,
        .text-muted,
        .form-text {
            color: var(--muted) !important;
        }

        /* Cards */
        .card-pro {
            border: 1px solid var(--border) !important;
            border-radius: var(--radius) !important;
            background: linear-gradient(180deg, var(--glassA), var(--glassB)) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .25) !important;
            backdrop-filter: blur(10px); /* Efecto vidrio real */
            -webkit-backdrop-filter: blur(10px);
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
            color: #fff !important;
        }

        .titulo-seccion {
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.5px;
        }

        /* Inputs (táctil, dark) */
        .form-control,
        .form-select,
        .input-group-text {
            border-radius: 16px !important;
            background: rgba(15, 23, 42, 0.6) !important; /* Fondo más oscuro */
            border: 1px solid rgba(255, 255, 255, .18) !important;
            color: #fff !important;
        }
        
        /* Corrección Inputs Deshabilitados */
        .form-control:disabled, .form-select:disabled {
            background: rgba(255, 255, 255, 0.05) !important;
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-control-lg,
        .input-group-text,
        .form-select-lg {
            padding: 12px 16px !important;
            font-size: 1rem !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, .50) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(59, 130, 246, .75) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .15) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .form-select option {
            background: #0f1a2f;
            color: #fff;
        }

        .form-control,
        .form-select {
            color-scheme: dark;
        }

        /* Labels & Texts */
        .info-label {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, .68);
            font-weight: 800;
        }

        .info-valor {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
        }

        /* Mini cards */
        .card-mini {
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, .12) !important;
            background: rgba(255, 255, 255, .05) !important;
            box-shadow: 0 12px 26px rgba(0, 0, 0, .20);
            color: #fff !important;
        }

        /* ===== Cobro compacto ===== */
        .cobro-card {
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: linear-gradient(180deg, rgba(255, 255, 255, .08) 0%, rgba(255, 255, 255, .03) 100%);
        }

        .cobro-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .btn-icon-lg {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .06);
            color: #fff;
        }

        .cobro-total-mini {
            background: rgba(59, 130, 246, .12);
            border: 1px solid rgba(59, 130, 246, .22);
            border-radius: 18px;
            padding: 12px 14px;
        }

        .cobro-total-mini .monto {
            font-size: 2rem;
            font-weight: 900;
            color: rgba(255, 255, 255, .95);
            line-height: 1;
        }

        /* ===== FIX DESGLOSE JS (SOLUCIÓN PANTALLA BLANCA) ===== */
        /* Esto fuerza a que el contenido inyectado por JS sea oscuro */
        #bloque_detalles_cobro .card,
        #bloque_detalles_cobro .list-group-item,
        #bloque_detalles_cobro > div {
            background-color: rgba(0, 0, 0, 0.3) !important; /* Fondo oscuro */
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
        }
        
        #bloque_detalles_cobro .text-muted,
        #bloque_detalles_cobro small {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        /* Oculta fondos blancos si Bootstrap los pone por defecto */
        #bloque_detalles_cobro .bg-white {
            background-color: transparent !important;
        }

        /* ===== Accordion Pro ===== */
        .accordion-pro .accordion-item {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            background: transparent;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .22);
        }

        .accordion-pro .accordion-button {
            padding: 14px 16px;
            border-radius: 18px !important;
            font-weight: 900;
            background: linear-gradient(180deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .03));
            color: rgba(255, 255, 255, .92);
            border: 1px solid rgba(255, 255, 255, .12);
        }
        
        .accordion-pro .accordion-button:not(.collapsed) {
             background: rgba(59, 130, 246, .25);
             color: #fff;
        }

        .accordion-pro .accordion-button::after {
            filter: invert(1) grayscale(100%);
        }

        .accordion-pro .accordion-body {
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, .10);
            border-top: 0;
        }

        /* Sticky cobro */
        @media (min-width: 768px) {
            .col-cobro {
                position: sticky;
                top: 90px; /* Ajustado para no chocar con Navbar */
                align-self: start;
            }
        }

        /* Barra Flotante */
        .barra-accion {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(10, 18, 34, .85);
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, .10);
            box-shadow: 0 -10px 40px rgba(0, 0, 0, .4);
            z-index: 1050;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }

        .barra-accion.visible {
            transform: translateY(0);
        }
        
        /* Botones */
        .btn-primary {
            background-color: var(--bs-primary) !important;
            border-color: rgba(255,255,255,0.1) !important;
            font-weight: 800;
        }
        
        .btn-outline-secondary {
            border: 1px solid rgba(255, 255, 255, .2) !important;
            color: #fff !important;
        }
        
        /* Estilos específicos para los switches y bloques */
        .bloque-boleto-perdido {
             background: rgba(245, 158, 11, 0.1) !important;
             border-color: rgba(245, 158, 11, 0.3) !important;
        }
        .bloque-descuento {
             background: rgba(59, 130, 246, 0.1) !important;
             border-color: rgba(59, 130, 246, 0.3) !important;
        }
        .icono-alerta, .icono-descuento {
            width: 48px; height: 48px; display: grid; place-items: center; border-radius: 12px; font-size: 1.2rem;
        }
        .icono-alerta { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .icono-descuento { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }

    </style>
</head>

<body>
    <?php include __DIR__ . "/../app/componentes/Navbar.php"; ?>

    <div class="container py-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h2 titulo-seccion mb-0">
                    <i class="bi bi-box-arrow-right text-primary me-2"></i>Salida de Vehículos
                </h1>
                <p class="text-secondary mb-0 mt-1">Busque el ticket o placa para procesar el cobro.</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-dark border border-secondary text-white shadow-sm p-3 rounded-4">
                    <i class="bi bi-clock me-2 text-primary"></i>
                    <span id="reloj_sistema" class="font-monospace fs-6">--:--:--</span>
                </span>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-12">
                <div class="card-pro p-4 mb-4 mx-auto shadow-lg" style="max-width: 760px;">
                    <label class="form-label fw-bold mb-2">
                        <i class="bi bi-search me-2"></i>Escanear Ticket o Ingresar Placa
                    </label>

                    <div class="input-group input-group-lg">
                        <span class="input-group-text text-muted border-end-0">
                            <i class="bi bi-upc-scan"></i>
                        </span>

                        <input type="text"
                            class="form-control border-start-0 ps-2 fs-4 font-monospace fw-bold"
                            id="input_busqueda"
                            placeholder="ABC-123"
                            style="text-transform: uppercase; letter-spacing: 2px;"
                            autocomplete="off"
                            autofocus>

                        <button class="btn btn-primary px-4" type="button" id="btn_buscar">
                            <span class="d-none d-sm-inline">Buscar</span> <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12">

                <div id="panel_cobro" style="display: none;">

                    <div class="card-pro overflow-hidden">
                        <div class="card-header border-bottom p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 badge-estado mb-2">
                                        <i class="bi bi-ticket-detailed me-1"></i>
                                        Ticket #<span id="txt_ticket_id">--</span>
                                    </span>

                                    <h3 class="mb-0 fw-bold font-monospace" id="txt_placa">--- ---</h3>
                                </div>

                                <div class="text-end">
                                    <div class="text-muted small text-uppercase fw-bold">Tarifa Aplicada</div>
                                    <div class="fw-bold text-white fs-5" id="txt_tarifa_nombre">--</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="row g-4">

                                <div class="col-lg-6">
                                    <div class="row mb-4">
                                        <div class="col-6 border-end border-secondary border-opacity-25">
                                            <div class="info-label mb-1">
                                                <i class="bi bi-arrow-right-circle me-1 text-success"></i>Entrada
                                            </div>
                                            <div class="info-valor" id="txt_entrada">--:--</div>
                                            <small class="text-muted" id="txt_fecha_entrada">--/--/----</small>
                                        </div>
                                        <div class="col-6 ps-4">
                                            <div class="info-label mb-1">
                                                <i class="bi bi-arrow-left-circle me-1 text-danger"></i>Salida
                                            </div>
                                            <div class="info-valor" id="txt_salida">--:--</div>
                                            <small class="text-success fw-bold">Actual</small>
                                        </div>
                                    </div>

                                    <div class="card-mini p-4 text-center">
                                        <div class="info-label mb-2">
                                            <i class="bi bi-hourglass-split me-1"></i>Tiempo Total
                                        </div>
                                        <div class="fw-bold text-white display-6" id="txt_tiempo_total">
                                            -- h -- min
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-cobro">

                                    <div class="cobro-card p-3 mb-3">
                                        <div class="cobro-top mb-3">
                                            <div class="cobro-total-mini flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="label">
                                                        <i class="bi bi-cash-stack me-1"></i>Total a pagar
                                                    </div>
                                                </div>
                                                <div class="monto mt-1" id="txt_total">$0.00</div>

                                                <div class="subline text-success small mt-1" id="txt_descuento_resumen" style="display:none;">
                                                    <i class="bi bi-tag-fill me-1"></i>Ahorro: 
                                                    <span class="fw-bold" id="txt_descuento_monto">$0.00</span>
                                                </div>
                                            </div>

                                            <button class="btn btn-outline-light btn-icon-lg ms-2"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse_detalles_cobro"
                                                aria-expanded="false"
                                                aria-controls="collapse_detalles_cobro"
                                                id="btn_toggle_detalles"
                                                title="Ver desglose">
                                                <i class="bi bi-calculator fs-4"></i>
                                            </button>
                                        </div>

                                        <div class="collapse mb-3" id="collapse_detalles_cobro">
                                            <div id="bloque_detalles_cobro">
                                                </div>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-12">
                                                <div class="form-floating">
                                                    <input type="number"
                                                        class="form-control form-control-lg fw-bold fs-3 text-end"
                                                        id="input_recibido"
                                                        placeholder="0.00"
                                                        style="height: 70px;"
                                                        step="1">
                                                    <label for="input_recibido" style="color: rgba(255,255,255,.6) !important;">
                                                        <i class="bi bi-cash-coin me-2"></i>Recibido ($)
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="card-mini p-3 d-flex justify-content-between align-items-center bg-black bg-opacity-25">
                                                    <span class="fw-bold text-secondary">
                                                        <i class="bi bi-arrow-repeat me-2"></i>Cambio:
                                                    </span>
                                                    <span class="fw-bold fs-2 text-success" id="txt_cambio">$0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion accordion-pro" id="accordion_ajustes">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading_ajustes">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse_ajustes"
                                                    aria-expanded="false"
                                                    aria-controls="collapse_ajustes">
                                                    <i class="bi bi-sliders me-2"></i>Ajustes y Extras
                                                </button>
                                            </h2>

                                            <div id="collapse_ajustes" class="accordion-collapse collapse"
                                                aria-labelledby="heading_ajustes"
                                                data-bs-parent="#accordion_ajustes">
                                                <div class="accordion-body d-grid gap-3">

                                                    <div class="card-mini p-3 bloque-boleto-perdido" id="bloque_boleto_perdido" style="display:none;">
                                                        <div class="d-flex align-items-center justify-content-between gap-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="icono-alerta">
                                                                    <i class="bi bi-ticket-perforated-fill"></i>
                                                                </div>

                                                                <div>
                                                                    <div class="fw-bold text-warning">Boleto Perdido</div>
                                                                    <div class="small mt-1">
                                                                        Cargo: <span class="badge bg-warning text-dark fw-bold">+ $<span id="txt_monto_boleto">0.00</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-check form-switch form-switch-xl m-0">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="chk_boleto_perdido">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card-mini p-3 bloque-descuento" id="bloque_descuento" style="display:none;">
                                                        <div class="d-flex align-items-start gap-3">
                                                            <div class="icono-descuento mt-1">
                                                                <i class="bi bi-percent"></i>
                                                            </div>

                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <div class="fw-bold text-info">Descuento</div>
                                                                    <span class="badge rounded-pill bg-dark border border-secondary text-secondary" id="badge_descuento_estado">Inactivo</span>
                                                                </div>

                                                                <div class="row g-2">
                                                                    <div class="col-12">
                                                                        <select class="form-select" id="sel_descuento_tipo">
                                                                            <option value="">Seleccione tipo...</option>
                                                                            <option value="PORCENTAJE">% Porcentaje</option>
                                                                            <option value="MONTO">$ Monto Fijo</option>
                                                                            <option value="HORAS">Horas Gratis</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-6">
                                                                        <input type="number" class="form-control" id="input_descuento_valor" placeholder="Valor" step="0.01" min="0" disabled>
                                                                    </div>
                                                                    <div class="col-6">
                                                                         <div class="form-control bg-transparent border-0 text-secondary small d-flex align-items-center ps-0">
                                                                            <i class="bi bi-arrow-left me-1"></i> Cantidad
                                                                         </div>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <input type="text" class="form-control" id="input_descuento_motivo" placeholder="Motivo (Obligatorio)" maxlength="255" disabled>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div></div></div></div></div></div>
                        </div>
                    </div>

                </div>

                <div id="panel_vacio" class="text-center py-5">
                    <div class="mb-4">
                        <div style="width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="bi bi-car-front text-secondary opacity-50 display-3"></i>
                        </div>
                    </div>
                    <h4 class="text-white fw-bold">Esperando vehículo...</h4>
                    <p class="text-secondary">Utilice el escáner o escriba la placa.</p>
                </div>

            </div>
        </div>

    </div>

    <div class="barra-accion" id="barra_acciones">
        <div class="container" style="max-width: 1000px;">
            <div class="row align-items-center justify-content-between g-3">
                <div class="col-12 col-md-auto text-center text-md-start order-2 order-md-1">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start text-secondary small">
                        <i class="bi bi-info-circle me-2"></i>
                        <span>Confirme que el cliente recibió su cambio.</span>
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-3 order-1 order-md-2">
                    <button type="button" class="btn btn-lg btn-outline-secondary flex-fill" style="min-width: 140px;" id="btn_cancelar">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-lg btn-primary flex-fill px-4 shadow-lg" style="min-width: 220px;" id="btn_confirmar_salida" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i>COBRAR Y SALIR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.URL_BASE = "<?php echo defined('URL_BASE') ? URL_BASE : 'http://localhost/sistema_estacionamiento/'; ?>";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="module" src="../publico/js/modulos/salidas.js"></script>
</body>

</html>