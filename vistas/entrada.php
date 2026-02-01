<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Entrada | Estacionamiento Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ==========================================================
           ESTILO DEL DASHBOARD (SOFT GLASS / DARK MODE / SPACIOUS)
           (Aplicado a entrada.php)
           ========================================================== */
        :root {
            --radius: 24px;
            --bg1: #101a30;
            --bg2: #131f3a;
            --glassA: rgba(255, 255, 255, .09);
            --glassB: rgba(255, 255, 255, .04);
            --border: rgba(255, 255, 255, .12);
            --textMuted: rgba(255, 255, 255, .72);

            /* Bootstrap */
            --bs-primary: #3b82f6;
        }

        body {
            font-family: Inter, system-ui, -apple-system, sans-serif !important;
            background:
                radial-gradient(1200px 700px at 10% 10%, rgba(59, 130, 246, .10), transparent 60%),
                radial-gradient(900px 600px at 90% 15%, rgba(34, 197, 94, .07), transparent 65%),
                linear-gradient(160deg, var(--bg1), var(--bg2)) !important;
            color: #fff !important;
            padding-bottom: 80px;
            min-height: 100vh;
        }

        /* --- CARDS (equivalente a .card-ui del dashboard) --- */
        .card-pro {
            background: linear-gradient(180deg, var(--glassA), var(--glassB)) !important;
            border: 1px solid var(--border) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .25) !important;
            border-radius: var(--radius) !important;
            color: #fff !important;
        }

        /* --- TYPOGRAPHY --- */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .text-dark {
            color: #fff !important;
        }

        .text-muted,
        .hint {
            color: var(--textMuted) !important;
        }

        /* --- INPUTS --- */
        .form-control,
        .form-select {
            border-radius: 16px !important;
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            color: #fff !important;
            padding: 14px 18px !important;
            font-size: 1.05rem;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, .55) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(255, 255, 255, .4) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .15) !important;
            background: rgba(255, 255, 255, .09) !important;
        }

        label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--textMuted);
        }

        /* --- BOTONES (mismo feel que dashboard) --- */
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

        .btn-primary,
        .btn-dark {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            border: 0 !important;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            color: #fff !important;
        }

        /* --- Selector de tipo de vehículo (tema glass) --- */
        .tipo-vehiculo-card {
            cursor: pointer;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03)) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #fff !important;
        }

        .tipo-vehiculo-card:hover {
            transform: translateY(-4px);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.06)) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.3) !important;
        }

        .btn-check:checked+.tipo-vehiculo-card {
            border-color: rgba(59, 130, 246, .75) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .15), 0 10px 25px rgba(0, 0, 0, 0.25);
            transform: translateY(-4px);
        }

        /* --- Placa grande (mantiene tamaño, pero con estilo dashboard) --- */
        .placa-input {
            font-size: 3.5rem;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            border-radius: 20px !important;
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            color: #fff !important;
            padding: 15px !important;
            letter-spacing: .08em;
        }

        .placa-input:focus {
            border-color: rgba(255, 255, 255, .4) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .15) !important;
            background: rgba(255, 255, 255, .09) !important;
        }

        /* Inputs opcionales */
        .input-opcional {
            border-radius: 16px !important;
            padding: 12px 18px !important;
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            color: #fff !important;
        }

        .form-label-opc {
            font-weight: 700;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, .65);
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        /* Toast */
        .toast-pro {
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 15px;
            background: rgba(10, 18, 32, .85);
            backdrop-filter: blur(12px);
            color: white;
        }

        /* ==========================
   FIX: BLOQUE PAGO ADELANTADO
   ========================== */
        .payment-box {
            background: linear-gradient(180deg, rgba(255, 255, 255, .09), rgba(255, 255, 255, .04)) !important;
            border: 1px solid rgba(255, 255, 255, .12) !important;
            color: #fff !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .22) !important;
        }

        /* Línea separadora que en dark se ve muy dura o casi invisible */
        hr {
            border-color: rgba(255, 255, 255, .14) !important;
            opacity: 1 !important;
        }

        /* Switch (toggle) con look dark */
        .form-switch .form-check-input {
            background-color: rgba(255, 255, 255, .12) !important;
            border: 1px solid rgba(255, 255, 255, .22) !important;
        }

        .form-switch .form-check-input:checked {
            background-color: #3b82f6 !important;
            border-color: rgba(59, 130, 246, .9) !important;
        }

        .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .18) !important;
        }

        /* Inputs disabled: que no se vean “lavados” */
        .form-control:disabled,
        .form-select:disabled,
        .input-opcional:disabled {
            background: rgba(255, 255, 255, .04) !important;
            color: rgba(255, 255, 255, .55) !important;
            border-color: rgba(255, 255, 255, .12) !important;
        }

        /* Placeholders más claros en dark */
        .form-control::placeholder,
        .input-opcional::placeholder {
            color: rgba(255, 255, 255, .45) !important;
        }

        /* Labels dentro del bloque opcional */
        .form-label-opc {
            color: rgba(255, 255, 255, .70) !important;
        }

        /* (Opcional) Si todavía te aparece algo blanco por bootstrap */
        .bg-light {
            background: transparent !important;
        }

        .border {
            border-color: rgba(255, 255, 255, .12) !important;
        }

        /* ==========================
   FIX: SELECT / DROPDOWN EN DARK
   ========================== */

/* El select cerrado */
.form-select{
  background-color: rgba(255,255,255,.06) !important;
  border: 1px solid rgba(255,255,255,.18) !important;
  color: #fff !important;
}

/* Opciones (solo aplica en algunos navegadores) */
.form-select option{
  background: #0f1a2f;   /* dark sólido */
  color: #fff;
}

/* Forzar esquema de color dark en controles nativos (Chrome/Edge/Safari recientes) */
.form-select,
.form-control,
.payment-box{
  color-scheme: dark;
}

/* Si el dropdown se ve “pegado” o feo, mejora el look general del option */
.form-select option:checked{
  background: #1e66d0;
  color: #fff;
}

    </style>
</head>

<body>

    <script>
        const URL_BASE = "<?php echo URL_BASE; ?>";
    </script>

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
        <div class="row justify-content-center text-start">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="fw-bold h2"><i class="bi bi-box-arrow-in-right text-primary me-2"></i>Nueva Entrada</h1>
                    <p class="text-muted">Registro rápido optimizado para tabletas</p>
                </div>

                <form id="form_entrada">
                    <div class="row g-3 mb-4 text-center" id="grid_tarifas"></div>

                    <div class="card card-pro p-4 mb-4">
                        <div class="mb-4">
                            <label class="form-label-opc text-center w-100">Número de Placa (Obligatorio)</label>
                            <input type="text" name="placa" id="placa" class="form-control placa-input" placeholder="--- ---" required autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label-opc"><i class="bi bi-tags-fill me-1"></i> Marca (Opcional)</label>
                                <input type="text" name="marca" class="form-control input-opcional" placeholder="Ej. Nissan">
                            </div>
                            <div class="col-6">
                                <label class="form-label-opc"><i class="bi bi-palette-fill me-1"></i> Color (Opcional)</label>
                                <input type="text" name="color" class="form-control input-opcional" placeholder="Ej. Rojo">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="p-3 rounded-4 payment-box">

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="fw-bold"><i class="bi bi-cash-coin me-2"></i>Pago adelantado (opcional)</div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="chk_pago_adelantado" name="pago_adelantado_activo" value="1">
                                    <label class="form-check-label fw-semibold" for="chk_pago_adelantado">Registrar ahora</label>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label-opc"><i class="bi bi-currency-dollar me-1"></i> Monto</label>
                                    <input type="number" step="0.01" min="0" name="pago_adelantado_monto" id="pago_adelantado_monto" class="form-control form-control-lg input-opcional" placeholder="0.00" disabled>
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label-opc"><i class="bi bi-chat-left-text me-1"></i> Concepto</label>
                                    <select name="pago_adelantado_concepto" id="pago_adelantado_concepto" class="form-select form-select-lg input-opcional" disabled>
                                        <option value="">Seleccione...</option>
                                        <option value="SOLO_EXTRA_NOCHE">Solo extra noche</option>
                                        <option value="HORARIO_MAS_EXTRA_NOCHE">Horario + extra noche</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-opc"><i class="bi bi-journal-text me-1"></i> Nota</label>
                                    <input type="text" name="pago_adelantado_nota" id="pago_adelantado_nota" class="form-control form-control-lg input-opcional" placeholder="Opcional" maxlength="120" disabled>
                                </div>
                            </div>

                            <div class="small text-muted mt-2">
                                <i class="bi bi-info-circle me-1"></i>En salida se mostrará como “pagado” y se cobrará solo el excedente.
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg py-4 rounded-pill shadow-lg fw-bold fs-3" id="btn_registrar">
                            <i class="bi bi-printer-fill me-2"></i> REGISTRAR E IMPRIMIR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../publico/js/modulos/entrada.js"></script>
</body>

</html>