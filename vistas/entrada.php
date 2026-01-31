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
        :root { --bs-primary: #4f46e5; --bs-body-bg: #f8fafc; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bs-body-bg); padding-bottom: 60px; }
        .card-pro { border: none; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }

        .tipo-vehiculo-card {
            cursor: pointer; border: 2px solid #e2e8f0; border-radius: 20px;
            padding: 20px; background: white; transition: 0.3s ease;
            display: flex; flex-direction: column; align-items: center;
        }
        .btn-check:checked + .tipo-vehiculo-card {
            border-color: var(--bs-primary); background-color: #eef2ff; transform: translateY(-5px);
        }

        .placa-input {
            font-size: 3.5rem; font-weight: 800; text-transform: uppercase;
            text-align: center; border-radius: 20px; border: 3px solid #e2e8f0; padding: 15px;
        }

        /* Inputs opcionales */
        .input-opcional { border-radius: 12px; padding: 12px; border: 2px solid #e2e8f0; }
        .form-label-opc { font-weight: 700; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; display: block; }

        .toast-pro { border: none; border-radius: 15px; background: #1e293b; color: white; }
    </style>
</head>
<body>

<script>const URL_BASE = "<?php echo URL_BASE; ?>";</script>

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

                    <div class="p-3 bg-light rounded-4 border">
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
<script src="../publico/js/modulos/entrada_vehiculos.js"></script>
</body>
</html>
