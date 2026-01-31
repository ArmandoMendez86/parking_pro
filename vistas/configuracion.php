<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Pro | Estacionamiento</title>
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
            padding-bottom: 120px;
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

        #ticket_mockup {
            background: white;
            width: 280px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            font-family: 'Courier New', monospace;
            border: 1px solid #ddd;
            min-height: 480px;
            transition: width 0.3s ease;
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
        }

        .sortable-item:hover {
            border-color: var(--bs-primary);
            background: #f5f3ff;
        }

        .sortable-item.dragging {
            opacity: 0.5;
            background: #eef2ff;
        }

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

        .ticket-dashed {
            border-top: 1px dashed #000;
            margin: 5px 0;
            width: 100%;
        }

        #barra_guardado {
            position: fixed;
            bottom: -120px;
            left: 0;
            right: 0;
            background: #1e293b;
            padding: 20px;
            transition: 0.4s;
            z-index: 1050;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        #barra_guardado.visible {
            bottom: 0;
        }

        /* =========================
   TICKET LIVE PREVIEW (PRO)
   Solo visual: no afecta lógica
   ========================= */

        #ticket_mockup {
            position: relative;
            border: 1px solid rgba(15, 23, 42, .12);
            border-radius: 18px;
            overflow: hidden;
            box-shadow:
                0 18px 45px rgba(15, 23, 42, .12),
                0 2px 8px rgba(15, 23, 42, .06);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .95), rgba(255, 255, 255, 1)),
                repeating-linear-gradient(0deg,
                    rgba(2, 6, 23, .02),
                    rgba(2, 6, 23, .02) 1px,
                    transparent 1px,
                    transparent 6px);
            padding-top: 16px !important;
        }

        /* Perforado superior e inferior tipo recibo */
        #ticket_mockup::before,
        #ticket_mockup::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 14px;
            background:
                radial-gradient(circle at 10px 50%, transparent 8px, rgba(15, 23, 42, .10) 9px, transparent 10px) repeat-x;
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

        /* Simula margen de papel */
        #ticket_mockup .val-block,
        #ticket_mockup .ticket-dashed {
            filter: saturate(1.02);
        }

        /* Bloques ordenables: más "card" y mejor hover */
        .sortable-item {
            border-radius: 12px;
            padding: 10px 12px;
            transition: transform .12s ease, box-shadow .12s ease, background .12s ease, border-color .12s ease;
            border: 1px solid transparent;
            margin: 4px 0;
        }

        .sortable-item:hover {
            background: rgba(99, 102, 241, .08);
            border-color: rgba(79, 70, 229, .35);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
            transform: translateY(-1px);
        }

        .sortable-item.dragging {
            background: rgba(99, 102, 241, .12);
            border-color: rgba(79, 70, 229, .45);
            box-shadow: 0 14px 30px rgba(15, 23, 42, .14);
            transform: scale(1.01);
            opacity: .95;
        }

        /* Texto del ticket (más "impreso") */
        .val-block {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            line-height: 1.25;
            color: rgba(2, 6, 23, .92);
            letter-spacing: .2px;
            text-rendering: geometricPrecision;
            -webkit-font-smoothing: antialiased;
        }

        /* Cuando el texto es "header", que se note más */
        #p_nombre_val {
            font-size: 15px !important;
            letter-spacing: .8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        #p_telefono_val,
        #p_direccion_val {
            opacity: .92;
        }

        /* Separador tipo ticket: más realista */
        .ticket-dashed {
            border: 0 !important;
            height: 10px;
            margin: 10px 0;
            background:
                repeating-linear-gradient(90deg,
                    rgba(2, 6, 23, .55) 0px,
                    rgba(2, 6, 23, .55) 8px,
                    transparent 8px,
                    transparent 14px);
            opacity: .55;
            border-radius: 2px;
        }

        /* Bloque "cuerpo" (PLACA/MARCA/FOLIO...) que se vea como sección */
        .sortable-item[data-id="p_cuerpo"] {
            background: rgba(2, 6, 23, .03);
            border: 1px solid rgba(2, 6, 23, .08);
            border-radius: 14px;
            padding: 12px;
        }

        .sortable-item[data-id="p_cuerpo"] .val-block {
            font-size: 11px;
            line-height: 1.35;
        }

        /* Controles flotantes más limpios */
        .controles-bloque {
            background: rgba(79, 70, 229, .92);
            backdrop-filter: blur(8px);
            box-shadow: 0 10px 25px rgba(15, 23, 42, .18);
            border: 1px solid rgba(255, 255, 255, .22);
        }

        .btn-edit {
            opacity: .95;
            transition: opacity .12s ease, transform .12s ease;
        }

        .btn-edit:hover {
            opacity: 1;
            transform: translateY(-1px);
        }

        /* Ajustes por ancho de papel para que siga viéndose "centrado" */
        .papel-58 {
            border-radius: 16px;
        }

        .papel-80 {
            border-radius: 18px;
        }

        /* ===============================
   FORMULARIO MÁS LIMPIO Y ESPACIADO
   Solo visual (no lógica)
   =============================== */

        /* Espaciado general entre filas */
        .row.g-3>div {
            margin-bottom: 14px;
        }

        /* Labels más claros */
        label.form-label {
            font-weight: 600;
            font-size: 13px;
            color: rgba(15, 23, 42, 0.85);
            margin-bottom: 6px;
            display: block;
        }

        /* Inputs más grandes y cómodos */
        .form-control,
        .form-select {
            border-radius: 12px !important;
            padding: 10px 12px !important;
            font-size: 14px;
            border: 1px solid rgba(15, 23, 42, 0.15);
            box-shadow: none;
            transition: all 0.15s ease;
        }

        /* Mejor foco */
        .form-control:focus,
        .form-select:focus {
            border-color: rgba(79, 70, 229, 0.55);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        /* Textareas más visibles */
        textarea.form-control {
            min-height: 90px;
            resize: vertical;
            line-height: 1.4;
            font-size: 14px;
            padding: 12px 14px !important;
        }

        /* Si ya tienen texto, que se vea elegante */
        textarea.form-control:not(:placeholder-shown) {
            background: rgba(99, 102, 241, 0.03);
        }

        /* Separar secciones del formulario */
        .card-body {
            padding: 22px !important;
        }

        /* Bloques dentro del card */
        .card-body hr {
            margin: 20px 0;
            opacity: 0.15;
        }

        /* Inputs pequeños como tolerancia o copias */
        input[type="number"].form-control-sm {
            padding: 8px 10px !important;
            font-size: 13px;
        }

        /* Mejor tabla de tarifas */
        #tabla_tarifas input {
            border-radius: 10px !important;
            padding: 7px 10px !important;
            font-size: 13px;
        }

        /* Botón añadir tarifa más visible */
        #btn_add_tarifa {
            border-radius: 12px;
            padding: 8px 14px;
            font-weight: 600;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .10);
        }

        /* Textareas de encabezado/pie como "ticket editor" */
        #encabezado_global,
        #pie_entrada,
        #pie_salida {
            font-family: ui-monospace, monospace;
            font-size: 13px;
            background: rgba(2, 6, 23, 0.02);
        }

        /* Que no se vea todo pegado */
        .form-check {
            margin-top: 6px;
        }

        /* Ajuste para switches */
        .form-switch .form-check-input {
            transform: scale(1.1);
            cursor: pointer;
        }
    </style>
</head>

<body class="text-start">
    <script>
        const URL_BASE = "<?php echo URL_BASE; ?>";
    </script>
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="notificacion_toast" class="toast align-items-center text-white bg-dark border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="mensaje_toast"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <form id="form_configuracion">
        <input type="hidden" name="estilos_ticket" id="estilos_ticket_input">

        <div class="container-fluid py-4 px-4">
            <div class="row g-4 text-start">
                <div class="col-lg-3 text-start">
                    <div class="nav nav-pills flex-column bg-white p-3 card-pro sticky-top" style="top:20px;" id="v-pills-tab" role="tablist">
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
                                    <div class="card card-pro p-4 bg-white mb-3 text-start">
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
                                            <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_pie_e" id="ver_pie_e"></div><textarea name="pie_entrada" id="pie_entrada" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3 text-start"><label class="small fw-bold text-danger">Pie Ticket Salida</label>
                                            <div class="form-check form-switch d-inline-block float-end"><input class="form-check-input" type="checkbox" name="ver_pie_s" id="ver_pie_s"></div><textarea name="pie_salida" id="pie_salida" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="card card-pro p-4 bg-white text-start">
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
                            <div class="card card-pro p-4 bg-white text-start">
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
                                        <thead class="table-light">
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
                            <div class="card card-pro p-4 bg-white text-start text-start text-start">
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
    <script src="../publico/js/modulos/configuracion_global.js"></script>
</body>

</html>