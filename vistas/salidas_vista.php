<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salidas | Control de Cobro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #4f46e5;
            --bs-primary-hover: #4338ca;
            --bs-body-bg: #f8fafc;
            --bs-text-gray: #64748b;
            --bs-card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bs-body-bg);
            padding-bottom: 140px;
        }

        .card-pro {
            border: none;
            border-radius: 20px;
            background: white;
            box-shadow: var(--bs-card-shadow);
            transition: all 0.3s ease;
        }

        .card-pro:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        .titulo-seccion {
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .form-control-lg,
        .input-group-text {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 1.1rem;
            border-color: #e2e8f0;
        }

        .form-select-lg {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 1.05rem;
            border-color: #e2e8f0;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .info-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--bs-text-gray);
            font-weight: 600;
        }

        .info-valor {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }

        .badge-estado {
            padding: 0.5em 1em;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .card-mini {
            border-radius: 16px;
            box-shadow: var(--bs-card-shadow);
            border: 0;
        }

        /* ===== Cobro compacto (menos scroll) ===== */
        .cobro-card {
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
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
            border-radius: 14px;
            display: grid;
            place-items: center;
        }

        .cobro-total-mini {
            background: #eef2ff;
            border: 1px solid rgba(79, 70, 229, 0.15);
            border-radius: 16px;
            padding: 12px 14px;
        }

        .cobro-total-mini .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            font-weight: 800;
        }

        .cobro-total-mini .monto {
            font-size: 2.1rem;
            font-weight: 900;
            color: var(--bs-primary);
            line-height: 1;
        }

        .cobro-total-mini .subline {
            margin-top: 6px;
            font-size: 0.9rem;
            color: #475569;
        }

        /* ===== Accordion Pro (Ajustes) ===== */
        .accordion-pro .accordion-item {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--bs-card-shadow);
        }

        .accordion-pro .accordion-button {
            padding: 14px 16px;
            border-radius: 16px !important;
            font-weight: 800;
        }

        .accordion-pro .accordion-button:not(.collapsed) {
            background: #f1f5f9;
            color: #0f172a;
            box-shadow: none;
        }

        .accordion-pro .accordion-body {
            padding: 14px 14px 16px;
            background: white;
        }

        /* Sticky cobro en tablet */
        @media (min-width: 768px) {
            .col-cobro {
                position: sticky;
                top: 16px;
                align-self: start;
            }
        }

        /* ===== BOLETO PERDIDO PRO ===== */
        .bloque-boleto-perdido {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
        }

        .bloque-boleto-perdido .icono-alerta {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            font-size: 1.35rem;
        }

        .form-switch-xl .form-check-input {
            width: 3.25rem;
            height: 1.75rem;
            margin-top: 0.35rem;
            cursor: pointer;
        }

        .form-switch-xl .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.18);
        }

        /* ===== DESCUENTO PRO ===== */
        .bloque-descuento {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%);
        }

        .bloque-descuento .icono-descuento {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(79, 70, 229, 0.12);
            color: #3730a3;
            font-size: 1.35rem;
        }

        /* Barra Flotante */
        .barra-accion {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            padding: 1.25rem;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .barra-accion.visible {
            transform: translateY(0);
        }
    </style>
</head>

<body>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h2 titulo-seccion mb-0">
                    <i class="bi bi-box-arrow-right text-primary me-2"></i>Salida de Vehículos
                </h1>
                <p class="text-secondary mb-0 mt-1">Busque el ticket o placa para procesar el cobro.</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-white text-dark shadow-sm p-3 rounded-4">
                    <i class="bi bi-clock me-2 text-primary"></i>
                    <span id="reloj_sistema">--:--:--</span>
                </span>
            </div>
        </div>

        <!-- Archivo: vistas/salidas/index.php -->
        <!-- COPIAR Y PEGAR EXACTAMENTE ESTE BLOQUE COMPLETO (incluye BOLETO PERDIDO + DESCUENTO) -->

        <div class="row g-4">

            <!-- Panel de Búsqueda (SIEMPRE centrado y con ancho controlado) -->
            <div class="col-12">
                <div class="card-pro p-4 mb-4 mx-auto" style="max-width: 760px;">
                    <label class="form-label fw-bold mb-2">
                        <i class="bi bi-search me-2"></i>Escanear Ticket o Ingresar Placa
                    </label>

                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="bi bi-upc-scan"></i>
                        </span>

                        <input type="text"
                            class="form-control border-start-0 ps-0"
                            id="input_busqueda"
                            placeholder="Ej. ABC-123 o Folio #42"
                            autocomplete="off"
                            autofocus>

                        <button class="btn btn-primary px-4" type="button" id="btn_buscar">
                            <i class="bi bi-search me-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Panel de Resultados (USA TODO EL ANCHO DISPONIBLE) -->
            <div class="col-12">

                <div id="panel_cobro" style="display: none;">

                    <div class="card-pro overflow-hidden">
                        <div class="card-header bg-white border-bottom p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary badge-estado mb-2">
                                        <i class="bi bi-ticket-detailed me-1"></i>
                                        Ticket #<span id="txt_ticket_id">--</span>
                                    </span>

                                    <h3 class="mb-0 fw-bold" id="txt_placa">--- ---</h3>
                                </div>

                                <div class="text-end">
                                    <div class="text-muted small">Tarifa Aplicada</div>
                                    <div class="fw-bold text-primary" id="txt_tarifa_nombre">--</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="row g-4">

                                <!-- Columna Tiempos -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="info-label mb-1">
                                            <i class="bi bi-arrow-right-circle me-1"></i>Entrada
                                        </div>
                                        <div class="info-valor" id="txt_entrada">--:--</div>
                                        <small class="text-muted" id="txt_fecha_entrada">--/--/----</small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="info-label mb-1">
                                            <i class="bi bi-arrow-left-circle me-1"></i>Salida (Actual)
                                        </div>
                                        <div class="info-valor" id="txt_salida">--:--</div>
                                    </div>

                                    <div class="card-mini p-3 bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="info-label mb-0">
                                                <i class="bi bi-hourglass-split me-1"></i>Tiempo Total
                                            </div>
                                            <div class="fw-bold text-primary fs-5" id="txt_tiempo_total">
                                                -- h -- min
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna Cobro -->
                                <div class="col-md-6 col-cobro">

                                    <div class="cobro-card p-3 mb-3">
                                        <div class="cobro-top">
                                            <div class="cobro-total-mini flex-grow-1">
                                                <div class="label">
                                                    <i class="bi bi-cash-stack me-1"></i>Total a pagar
                                                </div>
                                                <div class="monto" id="txt_total">$0.00</div>

                                                <div class="subline" id="txt_descuento_resumen" style="display:none;">
                                                    <i class="bi bi-tag me-1"></i>Descuento:
                                                    <span class="fw-bold" id="txt_descuento_monto">$0.00</span>
                                                </div>
                                            </div>

                                            <button class="btn btn-outline-primary btn-icon-lg"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse_detalles_cobro"
                                                aria-expanded="false"
                                                aria-controls="collapse_detalles_cobro"
                                                id="btn_toggle_detalles">
                                                <i class="bi bi-calculator fs-4"></i>
                                            </button>
                                        </div>

                                        <div class="collapse" id="collapse_detalles_cobro">
                                            <div id="bloque_detalles_cobro" class="mt-2"></div>
                                        </div>

                                        <div class="row g-2 mt-2">
                                            <div class="col-12 col-lg-7">
                                                <div class="form-floating">
                                                    <input type="number"
                                                        class="form-control form-control-lg fw-bold"
                                                        id="input_recibido"
                                                        placeholder="0.00"
                                                        step="10">
                                                    <label for="input_recibido">
                                                        <i class="bi bi-cash-coin me-2"></i>Monto Recibido ($)
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-5">
                                                <div class="card-mini p-3 bg-light d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-secondary">
                                                        <i class="bi bi-arrow-repeat me-2"></i>Cambio
                                                    </span>
                                                    <span class="fw-bold fs-4 text-success" id="txt_cambio">$0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ajustes colapsables: BOLETO PERDIDO + DESCUENTO -->
                                    <div class="accordion accordion-pro" id="accordion_ajustes">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading_ajustes">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse_ajustes"
                                                    aria-expanded="false"
                                                    aria-controls="collapse_ajustes">
                                                    <i class="bi bi-sliders me-2 text-primary"></i>Ajustes (opcional)
                                                </button>
                                            </h2>

                                            <div id="collapse_ajustes" class="accordion-collapse collapse"
                                                aria-labelledby="heading_ajustes"
                                                data-bs-parent="#accordion_ajustes">
                                                <div class="accordion-body d-grid gap-3">

                                                    <!-- BOLETO PERDIDO -->
                                                    <div class="card-mini p-3 bloque-boleto-perdido" id="bloque_boleto_perdido" style="display:none;">
                                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                                            <div class="d-flex align-items-start gap-3">
                                                                <div class="icono-alerta">
                                                                    <i class="bi bi-ticket-perforated"></i>
                                                                </div>

                                                                <div>
                                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                        <div class="fw-bold fs-5">Boleto perdido</div>

                                                                        <span class="badge rounded-pill bg-warning text-dark" id="badge_boleto_perdido">
                                                                            <i class="bi bi-exclamation-triangle me-1"></i>Aplica recargo
                                                                        </span>

                                                                        <span class="badge rounded-pill bg-light text-dark border" id="badge_monto_boleto">
                                                                            <i class="bi bi-cash-coin me-1"></i>+ $<span id="txt_monto_boleto">0.00</span>
                                                                        </span>
                                                                    </div>

                                                                    <div class="text-muted small mt-1">
                                                                        <i class="bi bi-info-circle me-1"></i>Se suma el recargo según el tipo de vehículo.
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-check form-switch form-switch-xl m-0">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="chk_boleto_perdido">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- DESCUENTO -->
                                                    <div class="card-mini p-3 bloque-descuento" id="bloque_descuento" style="display:none;">
                                                        <div class="d-flex align-items-start gap-3">
                                                            <div class="icono-descuento">
                                                                <i class="bi bi-tag"></i>
                                                            </div>

                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                                    <div class="fw-bold fs-5">Descuento</div>
                                                                    <span class="badge rounded-pill bg-white text-dark border" id="badge_descuento_estado">
                                                                        <i class="bi bi-slash-circle me-1"></i>Sin descuento
                                                                    </span>
                                                                </div>

                                                                <div class="row g-2 mt-2">
                                                                    <div class="col-12">
                                                                        <label class="form-label small text-muted fw-semibold mb-1">
                                                                            <i class="bi bi-list-check me-1"></i>Tipo
                                                                        </label>
                                                                        <select class="form-select form-select-lg" id="sel_descuento_tipo">
                                                                            <option value="">Sin descuento</option>
                                                                            <option value="PORCENTAJE">% Porcentaje</option>
                                                                            <option value="MONTO">$ Monto</option>
                                                                            <option value="HORAS">Horas</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <label class="form-label small text-muted fw-semibold mb-1">
                                                                            <i class="bi bi-123 me-1"></i>Valor
                                                                        </label>
                                                                        <input type="number" class="form-control form-control-lg" id="input_descuento_valor" placeholder="0" step="0.01" min="0" disabled>
                                                                        <div class="form-text" id="txt_descuento_hint">
                                                                            Seleccione un tipo para capturar el valor.
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <label class="form-label small text-muted fw-semibold mb-1">
                                                                            <i class="bi bi-chat-left-text me-1"></i>Motivo (opcional)
                                                                        </label>
                                                                        <input type="text" class="form-control form-control-lg" id="input_descuento_motivo" placeholder="Ej. Cortesía / Cliente frecuente" maxlength="255" disabled>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div><!-- /accordion-body -->
                                            </div><!-- /collapse -->
                                        </div><!-- /item -->
                                    </div><!-- /accordion -->

                                </div><!-- /col cobro -->

                            </div>
                        </div>
                    </div>

                </div>

                <!-- Estado Vacío -->
                <div id="panel_vacio" class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-car-front display-1 text-light"></i>
                    </div>
                    <h4 class="text-secondary">Esperando vehículo...</h4>
                </div>

            </div>
        </div>

    </div>

    <!-- Barra Flotante -->
    <div class="barra-accion" id="barra_acciones">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto d-none d-md-block">
                    <div class="d-flex align-items-center text-secondary">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Verifique el cambio antes de confirmar.</small>
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="button" class="btn btn-lg btn-outline-secondary flex-grow-1" id="btn_cancelar">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-lg btn-primary flex-grow-1 px-5" id="btn_confirmar_salida" disabled>
                        <i class="bi bi-cash-coin me-2"></i>Cobrar y Dar Salida
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