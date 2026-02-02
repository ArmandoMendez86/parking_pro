<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Pro | Estacionamiento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bs-primary: #4f46e5;
            --bs-body-bg: #f8fafc;
        }


        body {
            font-family: 'Inter', sans-serif;
            background: var(--bs-body-bg);
           
            width: 100%;
            height: 100vh;
        }

        .card-pro {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 600;
            text-align: left;
            border-radius: 12px !important;
            margin-bottom: 5px;
        }

        .nav-pills .nav-link.active {
            background-color: var(--bs-primary) !important;
            color: white !important;
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.2);
        }

        /* TICKET MOCKUP - REGLAS ESPECIFICAS
           Importante: Forzamos color negro aquí porque simula papel,
           incluso si el tema global es oscuro.
        */
        #ticket_mockup {
            background: white !important;
            /* Siempre blanco (papel) */
            width: 280px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            font-family: 'Courier New', monospace;
            border: 1px solid #ddd;
            min-height: 480px;
            transition: width 0.3s ease;
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            padding-top: 16px !important;
            color: #000 !important;
            /* TEXTO NEGRO FORZADO PARA EL TICKET */
        }

        #ticket_mockup .val-block,
        #ticket_mockup h1,
        #ticket_mockup div {
            color: #000 !important;
            /* Asegurar que todo dentro sea negro */
        }

        .papel-58 {
            width: 200px !important;
        }

        .papel-80 {
            width: 280px !important;
        }

        .sortable-item {
            padding: 8px 12px;
            cursor: grab;
            position: relative;
            border: 1px dashed transparent;
            border-radius: 12px;
            margin: 4px 0;
            transition: all .12s ease;
        }

        .sortable-item:hover {
            border-color: var(--bs-primary);
            background: rgba(79, 70, 229, .1) !important;
            /* Hover visible en dark mode */
        }

        /* Controles flotantes */
        .controles-bloque {
            position: absolute;
            top: -15px;
            right: 5px;
            background: var(--bs-primary);
            padding: 2px 8px;
            border-radius: 20px;
            display: none;
            gap: 8px;
            z-index: 100;
        }

        .sortable-item:hover .controles-bloque {
            display: flex;
        }

        .btn-edit {
            color: white;
            border: none;
            background: none;
            font-size: 0.8rem;
            cursor: pointer;
            padding: 0;
        }

        /* Decoración ticket */
        #ticket_mockup::before,
        #ticket_mockup::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 14px;
            background: radial-gradient(circle at 10px 50%, transparent 8px, rgba(15, 23, 42, .10) 9px, transparent 10px) repeat-x;
            background-size: 20px 14px;
            pointer-events: none;
            opacity: .9;
        }

        #ticket_mockup::before {
            top: -7px;
        }

        #ticket_mockup::after {
            bottom: -7px;
            transform: rotate(180deg);
        }

        .ticket-dashed {
            border: 0 !important;
            height: 10px;
            margin: 10px 0;
            background: repeating-linear-gradient(90deg, rgba(2, 6, 23, .55) 0px, rgba(2, 6, 23, .55) 8px, transparent 8px, transparent 14px);
            opacity: .55;
        }

        /* TEXTO DEL TICKET */
        .val-block {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.25;
            font-weight: 600;
            /* Un poco más grueso para legibilidad */
        }

        /* FORMULARIOS Y UI */
        .form-control,
        .form-select {
            border-radius: 12px !important;
            padding: 10px 12px !important;
        }

        #barra_guardado {
            position: fixed;
            bottom: -120px;
            left: 0;
            right: 0;
            padding: 20px;
            transition: 0.4s;
            z-index: 1050;
        }

        #barra_guardado.visible {
            bottom: 0;
        }

        /* ==========================================================
           THEME OVERRIDE (SOFT GLASS / DARK MODE)
           ========================================================== */
        :root {
            --radius: 20px;
            --bg1: #101a30;
            --bg2: #131f3a;
            --glassA: rgba(255, 255, 255, .09);
            --glassB: rgba(255, 255, 255, .04);
            --border: rgba(255, 255, 255, .12);
            --textMuted: rgba(255, 255, 255, .72);
        }

        body {
            font-family: Inter, sans-serif !important;
            background:
                radial-gradient(1200px 700px at 10% 10%, rgba(59, 130, 246, .10), transparent 60%),
                radial-gradient(900px 600px at 90% 15%, rgba(34, 197, 94, .07), transparent 65%),
                linear-gradient(160deg, var(--bg1), var(--bg2)) !important;
            color: #fff !important;
            /* Texto global blanco */
            background-attachment: fixed;
        }

        /* Corrección para Cards: Glass effect */
        .card-pro {
            background: linear-gradient(180deg, var(--glassA), var(--glassB)) !important;
            border: 1px solid var(--border) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .22) !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .text-dark,
        label {
            color: #fff !important;
        }

        .text-muted {
            color: var(--textMuted) !important;
        }

        /* Nav Pills */
        .nav-pills .nav-link {
            color: rgba(255, 255, 255, .82) !important;
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .12) !important;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            border: 0 !important;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4) !important;
        }

        /* Inputs oscuros */
        .form-control,
        .form-select,
        .form-control:disabled,
        .form-control[readonly] {
            background: rgba(0, 0, 0, .2) !important;
            border: 1px solid rgba(255, 255, 255, .15) !important;
            color: #fff !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(255, 255, 255, .4) !important;
            box-shadow: 0 0 0 .25rem rgba(59, 130, 246, .2) !important;
        }

        /* Tablas Oscuras */
        .table {
            --bs-table-color: #fff;
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.1);
        }

        .table thead th {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }

        .table td,
        .table th {
            background: transparent !important;
            color: #fff !important;
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        #barra_guardado {
            background: rgba(10, 18, 32, .85) !important;
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, .14) !important;
        }
    </style>
</head>

<body class="text-start">
     <?php include __DIR__ . "/../app/componentes/Navbar.php"; ?>
    <script>
        const URL_BASE = "<?php echo URL_BASE; ?>";
    </script>
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="notificacion_toast" class="toast align-items-center text-white bg-dark border-0" role="alert">
            <div class="d-flex p-3">
                <div class="toast-body d-flex align-items-center gap-2" id="mensaje_toast"></div>
                 <i id="icono_toast" class="bi bi-check-circle-fill text-success fs-4"></i>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <form id="form_configuracion">
        <input type="hidden" name="estilos_ticket" id="estilos_ticket_input">

        <div class="container-fluid py-4 px-4 mt-4">
            <div class="row g-4 text-start">
                <div class="col-lg-3 text-start">
                    <div class="nav nav-pills flex-column p-3 card-pro sticky-top" style="top:20px;" id="v-pills-tab" role="tablist">
                        <button class="nav-link active" id="tab-diseno" data-bs-toggle="pill" data-bs-target="#sec-hardware" type="button" role="tab"><i class="bi bi-printer me-2"></i>Diseño Ticket</button>
                        <button class="nav-link" id="tab-tarifas" data-bs-toggle="pill" data-bs-target="#sec-tarifas" type="button" role="tab"><i class="bi bi-cash-stack me-2"></i>Tarifas</button>
                        <button class="nav-link" id="tab-horarios" data-bs-toggle="pill" data-bs-target="#sec-horarios" type="button" role="tab"><i class="bi bi-clock me-2"></i>Horarios</button>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="sec-hardware" role="tabpanel">
                            <div class="row g-4 text-start">
                                <div class="col-md-7 text-start">
                                    <div class="card card-pro p-4 mb-3 text-start">
                                        <h6 class="fw-bold text-primary mb-3 text-uppercase">Datos y Visibilidad</h6>
                                        <div class="row g-3 mb-3 text-start">
                                            <div class="col-md-8 text-start"><label class="small fw-bold">Nombre</label>
                                                <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_nombre" id="ver_nombre"></div><input type="text" name="nombre_negocio" id="nombre_negocio" class="form-control">
                                            </div>
                                            <div class="col-md-4 text-start"><label class="small fw-bold">Teléfono</label>
                                                <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_telefono" id="ver_telefono"></div><input type="text" name="telefono_negocio" id="telefono_negocio" class="form-control">
                                            </div>
                                        </div>
                                        <div class="mb-3 text-start"><label class="small fw-bold">Dirección</label>
                                            <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_direccion" id="ver_direccion"></div><input type="text" name="direccion" id="direccion" class="form-control">
                                        </div>
                                        <div class="mb-3 text-start"><label class="small fw-bold text-primary">Encabezado</label>
                                            <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_encabezado" id="ver_encabezado"></div><textarea name="encabezado_global" id="encabezado_global" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3 text-start"><label class="small fw-bold text-success">Pie Ticket Entrada</label>
                                            <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_pie_e" id="ver_pie_e"></div><textarea name="pie_entrada" id="pie_entrada" class="form-control" rows="6"></textarea>
                                        </div>
                                        <div class="mb-3 text-start"><label class="small fw-bold text-danger">Pie Ticket Salida</label>
                                            <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_pie_s" id="ver_pie_s"></div><textarea name="pie_salida" id="pie_salida" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="card card-pro p-4 text-start">
                                        <h6 class="fw-bold text-primary mb-3 text-uppercase text-start">IMPRESORA</h6>
                                        <div class="row g-2 text-start">
                                            <div class="col-7 text-start"><label class="small fw-bold">Nombre</label><input type="text" name="nombre_impresora" id="nombre_impresora" class="form-control"></div>
                                            <div class="col-3 text-start"><label class="small fw-bold">Papel</label><select name="papel_tipo" id="papel_tipo" class="form-select">
                                                    <option value="58">58mm</option>
                                                    <option value="80">80mm</option>
                                                </select></div>
                                            <div class="col-2 text-start"><label class="small fw-bold">Copias</label><input type="number" name="copias" id="copias" class="form-control"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div id="ticket_mockup" class="p-3 papel-80">
                                        <div id="contenedor_orden" class="d-flex flex-column text-start">
                                            <?php
                                            $bloques = ['p_nombre' => 'NOMBRE NEGOCIO', 'p_telefono' => 'TEL: 000', 'p_direccion' => 'Dirección', 'p_encabezado' => 'Bienvenidos', 'p_pie' => '¡Gracias!'];
                                            foreach ($bloques as $id => $label): ?>
                                                <div class="sortable-item" draggable="true" data-id="<?php echo $id; ?>" data-align="center" data-size="10">
                                                    <div class="controles-bloque">
                                                        <button type="button" class="btn-edit" onclick="setAlign('<?php echo $id; ?>','left')"><i class="bi bi-text-left"></i></button>
                                                        <button type="button" class="btn-edit" onclick="setAlign('<?php echo $id; ?>','center')"><i class="bi bi-text-center"></i></button>
                                                        <button type="button" class="btn-edit" onclick="setAlign('<?php echo $id; ?>','right')"><i class="bi bi-text-right"></i></button>
                                                        <button type="button" class="btn-edit" onclick="setSize('<?php echo $id; ?>',1)"><i class="bi bi-plus-circle"></i></button>
                                                        <button type="button" class="btn-edit" onclick="setSize('<?php echo $id; ?>',-1)"><i class="bi bi-dash-circle"></i></button>
                                                    </div>
                                                    <div id="<?php echo $id; ?>_val" class="val-block text-center"><?php echo $label; ?></div>
                                                </div>
                                                <?php if ($id == 'p_encabezado'): ?>
                                                    <div class="sortable-item" draggable="true" data-id="p_cuerpo">
                                                        <div class="ticket-dashed"></div>
                                                        <div class="val-block px-1 small">
                                                            PLACA: ABC-123<br>
                                                            MARCA: NISSAN GRIS<br>
                                                            FOLIO: 001<br>
                                                            INGRESO: <?php echo date('d/m/Y H:i'); ?>
                                                        </div>
                                                        <div class="ticket-dashed"></div>
                                                    </div>
                                            <?php endif;
                                            endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="sec-tarifas" role="tabpanel">
                            <div class="card card-pro p-4 text-start">
                                <h5 class="fw-bold text-primary mb-3 text-start">Tarifas</h5>
                                <div class="row g-3 mb-4 text-start">
                                    <div class="col-6"><label class="small fw-bold text-start">Moneda</label><input type="text" id="moneda" name="moneda" class="form-control"></div>
                                    <div class="col-6"><label class="small fw-bold text-start">Tolerancia (min)</label><input type="number" id="tolerancia_entrada" name="tolerancia_entrada" class="form-control"></div>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="fw-bold m-0">Precios por Vehículo</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="btn_add_tarifa">+ Añadir Tarifa</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr class="small text-uppercase">
                                                <th>Tipo</th>
                                                <th>Costo Hora</th>
                                                <th>Fracción</th>
                                                <th>Tolerancia Extra</th>
                                                <th>Perdido</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="cuerpo_tarifas"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="sec-horarios" role="tabpanel">
                            <div class="card card-pro p-4 text-start text-start text-start">
                                <h5 class="fw-bold text-primary mb-4 text-start">Horarios</h5>
                                <table class="table">
                                    <tbody id="lista_horarios"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="barra_guardado" class="text-center"><button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">GUARDAR CONFIGURACIÓN</button></div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../publico/js/modulos/configuracion.js"></script>
</body>

</html>