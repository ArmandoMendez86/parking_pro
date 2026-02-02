<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Entrada | Estacionamiento Pro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --radius: 24px;
            --bg1: #101a30;
            --bg2: #131f3a;
            --glassA: rgba(255, 255, 255, .09);
            --glassB: rgba(255, 255, 255, .04);
            --border: rgba(255, 255, 255, .12);
            --textMuted: rgba(255, 255, 255, .72);
            --bs-primary: #3b82f6;
        }

        body {
            font-family: Inter, system-ui, -apple-system, sans-serif !important;
            background:
                radial-gradient(1200px 700px at 10% 10%, rgba(59, 130, 246, .10), transparent 60%),
                radial-gradient(900px 600px at 90% 15%, rgba(34, 197, 94, .07), transparent 65%),
                linear-gradient(160deg, var(--bg1), var(--bg2)) !important;
            color: #fff !important;
            padding-bottom: 96px;
            min-height: 100vh;
        }

        /* ===== Cards ===== */
        .card-pro {
            background: linear-gradient(180deg, var(--glassA), var(--glassB)) !important;
            border: 1px solid var(--border) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .22) !important;
            border-radius: var(--radius) !important;
            color: #fff !important;

            /* Compacto */
            padding: 20px !important;
        }


        /* ===== Typography ===== */
        .text-muted,
        .hint {
            color: var(--textMuted) !important;
        }

        /* ===== Inputs ===== */
        .form-control,
        .form-select {
            border-radius: 16px !important;
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            color: #fff !important;
            padding: 12px 16px !important;
            font-size: 1rem;
        }

        .form-control::placeholder,
        .input-opcional::placeholder {
            color: rgba(255, 255, 255, .45) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(255, 255, 255, .4) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .15) !important;
            background: rgba(255, 255, 255, .08) !important;
        }

        .form-control:disabled,
        .form-select:disabled,
        .input-opcional:disabled {
            background: rgba(255, 255, 255, .04) !important;
            color: rgba(255, 255, 255, .55) !important;
            border-color: rgba(255, 255, 255, .12) !important;
        }

        /* ===== Labels ===== */
        .form-label-opc {
            font-weight: 800;
            color: rgba(255, 255, 255, .80) !important;
            margin-bottom: 10px;
            font-size: 1.02rem;
        }

        /* ===== Placa ===== */
        .placa-input {
            text-transform: uppercase;
            letter-spacing: .12em;
            text-align: center;
            font-weight: 800;

            border-radius: 18px !important;
            font-size: 1.45rem !important;
            padding: 14px 14px !important;
        }

        /* ===== Tarifa cards ===== */
        #grid_tarifas .tipo-vehiculo-card {
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .05);
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
            min-height: 95px;
            padding: 14px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            user-select: none;
        }

        #grid_tarifas .tipo-vehiculo-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, .24);
            background: rgba(255, 255, 255, .07);
            box-shadow: 0 12px 26px rgba(0, 0, 0, .25);
        }

        .btn-check:checked+.tipo-vehiculo-card {
            border-color: rgba(59, 130, 246, .85) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .18), 0 14px 32px rgba(0, 0, 0, .30) !important;
            background: linear-gradient(180deg, rgba(59, 130, 246, .18), rgba(255, 255, 255, .06)) !important;
        }

        /* ===== Payment box ===== */
        .payment-box {
            background: rgba(255, 255, 255, .04);
            border: 1px dashed rgba(255, 255, 255, .18);
            padding: 14px !important;
        }

        /* ===== Switch ===== */
        .form-check-input {
            width: 3rem;
            height: 1.6rem;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
        }

        .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .18) !important;
        }

        /* ===== Button ===== */
        #btn_registrar {
            border-radius: 999px !important;
        }

        /* ===== Toast ===== */
        .toast-pro {
            border-radius: 18px !important;
            background: rgba(10, 18, 34, .75) !important;
            border: 1px solid rgba(255, 255, 255, .12) !important;
            color: #fff !important;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .35) !important;
            backdrop-filter: blur(10px);
        }

        /* ===== Select dropdown in dark ===== */
        .form-select option {
            background: #0f1a2f;
            color: #fff;
        }

        .form-control,
        .form-select,
        .payment-box {
            color-scheme: dark;
        }

        .form-select option:checked {
            background: #1e66d0;
            color: #fff;
        }

        /* ==========================================================
           UI PRO (solo visual, NO rompe lógica)
           ========================================================== */
        .header-card {
            position: relative;
            overflow: hidden;
        }

        .header-card:before {
            content: "";
            position: absolute;
            inset: -2px;
            background:
                radial-gradient(650px 220px at 15% 20%, rgba(59, 130, 246, .22), transparent 60%),
                radial-gradient(560px 260px at 85% 30%, rgba(34, 197, 94, .12), transparent 62%);
            pointer-events: none;
            opacity: .95;
        }

        .header-card>* {
            position: relative;
            z-index: 1;
        }

        .badge-soft {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(10px);
        }

        .step-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .55rem .85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .86);
            font-weight: 800;
            font-size: .85rem;
            user-select: none;
        }

        .section-head {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .10);
        }

        .divider-soft {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .18), transparent);
            border: 0;
            margin: 22px 0;
        }

        .hint-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            color: rgba(255, 255, 255, .70);
            font-size: .92rem;
            .hint-row {
    margin-top: 6px;
}

        }

        .chip-info {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem .9rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .06);
            color: rgba(255, 255, 255, .85);
            font-weight: 700;
            font-size: .9rem;
            user-select: none;
        }

        .micro-muted {
            color: rgba(255, 255, 255, .65);
            font-size: .9rem;
        }

        .lift-hover {
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .lift-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 42px rgba(0, 0, 0, .30) !important;
        }

        .btn-big {
            padding-top: 1.2rem !important;
            padding-bottom: 1.2rem !important;
        }

        /* ===== Pago adelantado colapsable ===== */
        .pago-toggle {
            border: 1px solid rgba(255, 255, 255, .10) !important;
            background: rgba(255, 255, 255, .05) !important;
            border-radius: 18px !important;
            padding: 16px 16px !important;
        }

        .pago-toggle:hover {
            background: rgba(255, 255, 255, .07) !important;
        }

        .chip-mini {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .6rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .06);
            font-size: .78rem;
            font-weight: 800;
            color: rgba(255, 255, 255, .86);
        }

        .pago-toggle[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }

        .chevron {
            transition: transform .15s ease;
        }
    </style>
</head>

<body>
     <?php include __DIR__ . "/../app/componentes/Navbar.php"; ?>
    <script>
        const URL_BASE = "<?php echo URL_BASE; ?>";
    </script>

    <!-- Toast (IDs intactos para tu JS) -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="notificacion_toast" class="toast toast-pro" role="alert">
            <div class="d-flex p-3">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="icono_toast" class="bi bi-check-circle-fill text-success fs-4"></i>
                    <span id="mensaje_toast" class="fw-semibold"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Header PRO -->
                <div class="header-card card-pro p-4 p-md-5 mb-4 lift-hover">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill badge-soft mb-3">
                                <i class="bi bi-lightning-charge-fill text-warning"></i>
                                <span class="fw-semibold">Entrada rápida</span>
                            </div>

                            <h1 class="fw-bold display-6 m-0">
                                <i class="bi bi-box-arrow-in-right text-primary me-2"></i>Nueva Entrada
                            </h1>
                            <div class="text-muted mt-2">
                                Optimizado para tablet/táctil · registra e imprime en segundos
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-start justify-content-md-end">
                            <span class="step-chip"><i class="bi bi-car-front-fill me-2"></i>1) Tarifa</span>
                            <span class="step-chip"><i class="bi bi-keyboard-fill me-2"></i>2) Datos</span>
                            <span class="step-chip"><i class="bi bi-cash-coin me-2"></i>3) Adelanto</span>
                        </div>
                    </div>
                </div>

                <form id="form_entrada">

                    <!-- Tarifa -->
                    <div class="card card-pro p-3 p-md-4 mb-3 lift-hover">

                        <div class="section-head mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-car-front-fill text-primary fs-4"></i>
                                <div>
                                    <div class="fw-bold">Selecciona el tipo de vehículo</div>
                                    <div class="text-muted small">Toca una tarjeta para elegir la tarifa</div>
                                </div>
                            </div>
                        </div>

                        <!-- JS llena este contenedor (ID intacto) -->
                        <div class="row g-3 text-center d-flex justify-content-center p-2" id="grid_tarifas"></div>

                        <div class="hint-row">
                            <i class="bi bi-hand-index-thumb"></i>
                            <span>Tip: puedes cambiar la tarifa antes de imprimir</span>
                        </div>
                    </div>

                    <!-- Datos + Pago adelantado -->
                    <div class="card card-pro p-4 p-md-5 mb-4 lift-hover">
                        <div class="section-head mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-keyboard-fill text-primary fs-4"></i>
                                <div>
                                    <div class="fw-bold">Datos del vehículo</div>
                                    <div class="text-muted small">La placa es obligatoria · marca y color opcionales</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-opc text-center w-100">
                                <i class="bi bi-credit-card-2-front-fill me-2"></i>Número de Placa (Obligatorio)
                            </label>
                            <input type="text" name="placa" id="placa" class="form-control placa-input" placeholder="--- ---" required autocomplete="off">
                            <div class="text-muted small text-center mt-2">
                                <i class="bi bi-info-circle me-1"></i>Consejo: escribe sin guiones si prefieres (ej. ABC123)
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label-opc">
                                    <i class="bi bi-tags-fill me-1"></i> Marca (Opcional)
                                </label>
                                <input type="text" name="marca" class="form-control form-control-lg input-opcional" placeholder="Ej. Nissan">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label-opc">
                                    <i class="bi bi-palette-fill me-1"></i> Color (Opcional)
                                </label>
                                <input type="text" name="color" class="form-control form-control-lg input-opcional" placeholder="Ej. Rojo">
                            </div>
                        </div>

                        <hr class="divider-soft">

                        <!-- PAGO ADELANTADO (colapsable) -->
                        <button
                            type="button"
                            class="btn w-100 text-start section-head d-flex align-items-center justify-content-between gap-3 pago-toggle"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse_pago_adelantado"
                            aria-expanded="false"
                            aria-controls="collapse_pago_adelantado">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-coin fs-4 text-white"></i>
                                <div>
                                    <div class="fw-bold d-flex align-items-center gap-2 flex-wrap text-primary">
                                        Pago adelantado
                                        <span class="chip-mini"><i class="bi bi-stars me-1"></i>Opcional</span>
                                    </div>
                                    <div class="text-muted small">Toca para expandir y registrar anticipo</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span class="micro-muted d-none d-md-inline">Expandir</span>
                                <i class="bi bi-chevron-down fs-5 chevron"></i>
                            </div>
                        </button>

                        <div id="collapse_pago_adelantado" class="collapse mt-2">
                            <div class="p-3 p-md-4 rounded-4 payment-box">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-receipt-cutoff text-primary"></i>
                                        <div class="fw-bold">Registrar pago adelantado</div>
                                    </div>

                                    <!-- IDs intactos para tu JS -->
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="chk_pago_adelantado" name="pago_adelantado_activo" value="1">
                                        <label class="form-check-label fw-semibold" for="chk_pago_adelantado">Activar</label>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label-opc">
                                            <i class="bi bi-currency-dollar me-1"></i> Monto
                                        </label>
                                        <input type="number" step="0.01" min="0" name="pago_adelantado_monto" id="pago_adelantado_monto" class="form-control form-control-lg input-opcional" placeholder="0.00" disabled>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <label class="form-label-opc">
                                            <i class="bi bi-chat-left-text me-1"></i> Concepto
                                        </label>
                                        <select name="pago_adelantado_concepto" id="pago_adelantado_concepto" class="form-select form-select-lg input-opcional" disabled>
                                            <option value="">Seleccione...</option>
                                            <option value="SOLO_EXTRA_NOCHE">Solo extra noche</option>
                                            <option value="HORARIO_MAS_EXTRA_NOCHE">Horario + extra noche</option>
                                            <option value="OTRO">Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-opc">
                                            <i class="bi bi-journal-text me-1"></i> Nota
                                        </label>
                                        <input type="text" name="pago_adelantado_nota" id="pago_adelantado_nota" class="form-control form-control-lg input-opcional" placeholder="Opcional" maxlength="120" disabled>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
                                    <div class="micro-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        En salida se mostrará como “pagado” y se cobrará solo el excedente.
                                    </div>

                                    <div class="chip-info">
                                        <i class="bi bi-shield-check"></i>
                                        <span>Validación anti-duplicados</span>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Acción -->
                        <div class="card card-pro p-3 p-md-4 lift-hover mt-2">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-lg fw-bold fs-4 py-3" id="btn_registrar">
                                    <i class="bi bi-printer-fill me-2"></i> REGISTRAR E IMPRIMIR
                                </button>
                            </div>

                            <div class="hint-row">
                                <i class="bi bi-lightning-charge"></i>
                                <span>Listo para operar: grande, táctil y rápido</span>
                            </div>
                        </div>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../publico/js/modulos/entrada.js"></script>
</body>

</html>