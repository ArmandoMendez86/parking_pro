<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pensiones Pro | Estacionamiento</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-0: #050816;
            --bg-1: #07102a;
            --glass: rgba(255, 255, 255, .06);
            --glass-2: rgba(255, 255, 255, .08);
            --border: rgba(255, 255, 255, .14);
            --border-2: rgba(255, 255, 255, .22);
            --text: #e5e7eb;
            --muted: #a8b1c2;
            --muted-2: #93a4bd;
            --shadow: 0 10px 30px rgba(0, 0, 0, .35);
            --shadow-soft: 0 6px 18px rgba(0, 0, 0, .25);
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(1200px 600px at 18% 20%, rgba(59, 130, 246, .18), transparent 60%),
                radial-gradient(900px 500px at 80% 18%, rgba(14, 165, 233, .12), transparent 60%),
                radial-gradient(900px 600px at 70% 78%, rgba(99, 102, 241, .12), transparent 60%),
                linear-gradient(180deg, var(--bg-1), var(--bg-0));
            color: var(--text);
            padding-bottom: 160px;
            height: 100%;
        }

        a {
            color: inherit;
        }

        /* Títulos */
        .titulo-seccion {
            font-weight: 900;
            letter-spacing: -0.5px;
            color: var(--text);
            text-shadow: 0 1px 0 rgba(0, 0, 0, .35);
        }

        .subtexto {
            color: var(--muted);
        }

        /* Cards glass */
        .card-pro,
        .user-card {
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: var(--shadow);
        }

        .user-card {
            overflow: hidden;
        }

        .user-card .top {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .user-card .name {
            font-weight: 900;
            font-size: 1.08rem;
            color: var(--text);
            line-height: 1.15;
        }

        .user-card .meta {
            color: var(--muted);
            font-weight: 700;
            font-size: .92rem;
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .user-card .actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        /* Chips */
        .chip {
            border-radius: 999px;
            padding: .45rem .75rem;
            font-weight: 800;
            font-size: .85rem;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(0, 0, 0, .22);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            white-space: nowrap;
        }

        .chip-muted {
            color: var(--muted);
            background: rgba(255, 255, 255, .06);
        }

        .chip-ok {
            border-color: rgba(34, 197, 94, .35);
            background: rgba(34, 197, 94, .14);
            color: #bbf7d0;
        }

        .chip-off {
            border-color: rgba(239, 68, 68, .35);
            background: rgba(239, 68, 68, .14);
            color: #fecaca;
        }

        /* Controles: visibilidad máxima en dark */
        /* Labels: asegurar contraste en dark (no afecta funcionalidad) */
        label,
        .form-label,
        .col-form-label {
            color: rgba(234, 240, 255, .92) !important;
            font-weight: 800;
            letter-spacing: .1px;
        }

        .form-text,
        .form-text *,
        small,
        .small {
            color: rgba(168, 177, 194, .85) !important;
        }


        .form-control,
        .form-select,
        .input-group-text {
            background: rgba(255, 255, 255, .06) !important;
            border-color: rgba(255, 255, 255, .16) !important;
            color: var(--text) !important;
        }

        .form-control::placeholder {
            color: rgba(168, 177, 194, .75) !important;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 .25rem rgba(59, 130, 246, .25) !important;
            border-color: rgba(59, 130, 246, .55) !important;
        }

        .form-control-lg,
        .form-select-lg {
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }

        .input-group-text {
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }

        /* Select flecha visible */
        .form-select {
            background-image:
                linear-gradient(45deg, transparent 50%, rgba(229, 231, 235, .9) 50%),
                linear-gradient(135deg, rgba(229, 231, 235, .9) 50%, transparent 50%),
                linear-gradient(to right, transparent, transparent) !important;
            background-position:
                calc(100% - 20px) calc(1em + 2px),
                calc(100% - 15px) calc(1em + 2px),
                100% 0 !important;
            background-size: 5px 5px, 5px 5px, 2.5em 2.5em !important;
            background-repeat: no-repeat !important;
        }

        /* Opciones del select (evita texto claro sobre fondo claro) */
        .form-select option {
            background: rgba(13, 24, 43, 1) !important;
            color: rgba(234, 240, 255, .96) !important;
        }

        .form-select option:disabled {
            color: rgba(168, 177, 194, .75) !important;
        }

        .form-select {
            color-scheme: dark;
        }

        /* Checkbox / Switch */
        .form-check-label {
            color: var(--text);
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, .08) !important;
            border-color: rgba(255, 255, 255, .28) !important;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 .25rem rgba(59, 130, 246, .25) !important;
            border-color: rgba(59, 130, 246, .55) !important;
        }

        .form-check-input:checked {
            background-color: rgba(59, 130, 246, .9) !important;
            border-color: rgba(59, 130, 246, .95) !important;
        }

        .form-switch .form-check-input {
            background-image: none;
        }

        .form-switch .form-check-input:not(:checked) {
            background-color: rgba(148, 163, 184, .25) !important;
        }

        .form-switch .form-check-input:checked {
            background-color: rgba(59, 130, 246, .9) !important;
        }

        .form-switch .form-check-input:disabled {
            opacity: .55;
            cursor: not-allowed !important;
        }

        /* Botones */
        .btn-lg {
            border-radius: 14px;
            padding: .9rem 1.1rem;
            font-weight: 800;
        }

        .btn-outline-secondary {
            color: var(--text);
            border-color: rgba(255, 255, 255, .22);
        }

        .btn-outline-secondary:hover {
            background: rgba(255, 255, 255, .10);
            border-color: rgba(255, 255, 255, .28);
            color: var(--text);
        }

        .btn-outline-danger {
            color: #fecaca;
            border-color: rgba(239, 68, 68, .45);
        }

        .btn-outline-danger:hover {
            background: rgba(239, 68, 68, .16);
            color: #fee2e2;
            border-color: rgba(239, 68, 68, .55);
        }

        .btn-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            padding: 0;
        }

        /* Hint */
        .hint {
            font-size: .9rem;
            color: var(--muted);
            font-weight: 700;
        }

        /* Barra inferior */
        .barra-accion {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(10, 16, 40, .72);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255, 255, 255, .12);
            padding: 1.1rem;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, .35);
            z-index: 1000;
            transform: translateY(110%);
            transition: transform .25s ease;
        }

        .barra-accion.visible {
            transform: translateY(0);
        }

        /* Layout */
        @media (min-width: 992px) {
            .layout-grid {
                display: grid;
                grid-template-columns: 1.25fr .75fr;
                gap: 20px;
                align-items: start;
            }

            .form-sticky {
                position: sticky;
                top: 16px;
            }

            .list-grid {
                grid-template-columns: 1fr !important;
                gap: 18px !important;
            }
        }

        @media (min-width: 1200px) {
            .list-grid {
                grid-template-columns: 1fr 1fr;
                gap: 18px;
            }
        }

        /* Meta chips en grid para que no se apilen feo */
        .user-card .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        @media (max-width: 1200px) {
            .user-card .meta {
                grid-template-columns: 1fr;
            }
        }

        .user-card .meta .chip {
            width: 100%;
            justify-content: flex-start;
        }

        /* Iconos azules: que destaquen en dark */
        .text-primary {
            color: #60a5fa !important;
        }

        .text-success {
            color: #34d399 !important;
        }

        .text-danger {
            color: #fb7185 !important;
        }

        /* Pills / menú lateral */
        .nav-pills .nav-link {
            color: var(--muted);
            font-weight: 700;
            text-align: left;
            border-radius: 14px !important;
            margin-bottom: 8px;
            padding: 12px 14px;
            background: transparent;
            border: 1px solid transparent;
        }

        .nav-pills .nav-link:hover {
            color: var(--text);
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .12);
        }

        .nav-pills .nav-link.active {
            background: rgba(59, 130, 246, .22) !important;
            color: #eaf2ff !important;
            border-color: rgba(59, 130, 246, .35) !important;
            box-shadow: 0 10px 22px rgba(0, 0, 0, .25);
        }

        /* Badges */
        .badge-soft {
            background: rgba(255, 255, 255, .08);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, .14);
            padding: .45rem .7rem;
            border-radius: 999px;
            font-weight: 700;
        }

        .badge-estado {
            background: rgba(255, 255, 255, .08);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 999px;
            padding: .25rem .6rem;
            font-weight: 700;
        }

        /* Botones */
        .btn-soft {
            background: rgba(59, 130, 246, .14);
            color: #dbeafe;
            border: 1px solid rgba(59, 130, 246, .25);
        }

        .btn-soft:hover {
            background: rgba(59, 130, 246, .18);
            border-color: rgba(59, 130, 246, .32);
            color: #eff6ff;
        }

        /* Tablas (fix: fondo oscuro real + texto visible) */
        .table {
            color: var(--text) !important;
            --bs-table-color: var(--text);
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, .12);
            --bs-table-striped-color: var(--text);
            --bs-table-striped-bg: rgba(255, 255, 255, .04);
            --bs-table-hover-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .06);
        }

        /* Bootstrap 5 pinta celdas con background por variable: lo forzamos a transparente */
        .table> :not(caption)>*>* {
            background-color: transparent !important;
            color: var(--text) !important;
        }

        .table thead th {
            color: var(--muted) !important;
            background: rgba(255, 255, 255, .06) !important;
            border-bottom: 1px solid rgba(255, 255, 255, .16) !important;
        }

        .table tbody td,
        .table tbody th {
            border-top: 1px solid rgba(255, 255, 255, .10) !important;
        }

        .table-hover tbody tr:hover {
            background: rgba(255, 255, 255, .04) !important;
        }

        /* Si algún template trae table-light/bg-white */
        .table-light,
        .table.table-light {
            --bs-table-bg: transparent !important;
            --bs-table-color: var(--text) !important;
        }

        /* Ajustes de textos bootstrap */
        .text-secondary {
            color: var(--muted) !important;
        }

        .text-muted {
            color: var(--muted) !important;
        }

        /* Dropdown nativo (select options) */
        select option {
            background: #0b1736;
            color: #e5e7eb;
        }


        .bg-white,
        .bg-light {
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .14);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }



        /* =========================
           FIX VISIBILIDAD TOTAL (labels/textos/tablas)
           No afecta funcionalidad (solo CSS)
           ========================= */

        /* Asegurar contraste de títulos y textos dentro de tarjetas */
        .card-pro,
        .card-pro .card-body {
            color: var(--text) !important;
        }

        .card-pro h1,
        .card-pro h2,
        .card-pro h3,
        .card-pro h4,
        .card-pro h5,
        .card-pro h6,
        .card-pro .fw-bold,
        .card-pro .fw-semibold,
        .card-pro .section-title {
            color: rgba(234, 240, 255, .96) !important;
        }

        .card-pro .subtexto,
        .card-pro .text-secondary,
        .card-pro .text-muted,
        .card-pro .small,
        .card-pro small,
        .card-pro .form-text {
            color: rgba(168, 177, 194, .92) !important;
        }

        /* Pills / Tabs del panel izquierdo */
        .nav-pills .nav-link {
            color: rgba(234, 240, 255, .92) !important;
            background: rgba(255, 255, 255, .04) !important;
            border: 1px solid rgba(255, 255, 255, .10) !important;
            border-radius: 14px;
            padding: 12px 14px;
        }

        .nav-pills .nav-link:hover {
            background: rgba(255, 255, 255, .06) !important;
            border-color: rgba(255, 255, 255, .16) !important;
        }

        .nav-pills .nav-link.active {
            background: rgba(59, 130, 246, .22) !important;
            border-color: rgba(59, 130, 246, .42) !important;
            color: rgba(234, 240, 255, .98) !important;
            box-shadow: 0 0 0 .25rem rgba(59, 130, 246, .12);
        }

        /* Inputs / selects / textarea: asegurar texto + placeholder + estados */
        .form-control,
        .form-select,
        textarea.form-control {
            color: rgba(234, 240, 255, .96) !important;
        }

        .form-control::placeholder,
        textarea.form-control::placeholder {
            color: rgba(168, 177, 194, .78) !important;
            opacity: 1;
        }

        .form-control:disabled,
        .form-select:disabled {
            color: rgba(234, 240, 255, .65) !important;
            background: rgba(255, 255, 255, .035) !important;
            border-color: rgba(255, 255, 255, .12) !important;
        }

        /* Check / switch: visibilidad en dark */
        .form-check-label {
            color: rgba(234, 240, 255, .92) !important;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, .10) !important;
            border-color: rgba(255, 255, 255, .20) !important;
        }

        .form-check-input:checked {
            background-color: rgba(59, 130, 246, .95) !important;
            border-color: rgba(59, 130, 246, .95) !important;
        }

        .form-switch .form-check-input {
            width: 3.2em;
            height: 1.6em;
        }

        /* Tablas Bootstrap: evitar fondo blanco y asegurar contraste */
        .table {
            --bs-table-bg: transparent !important;
            --bs-table-color: rgba(234, 240, 255, .94) !important;
            --bs-table-border-color: rgba(255, 255, 255, .10) !important;
            --bs-table-striped-bg: rgba(255, 255, 255, .03) !important;
            --bs-table-striped-color: rgba(234, 240, 255, .94) !important;
            --bs-table-active-bg: rgba(59, 130, 246, .10) !important;
            --bs-table-active-color: rgba(234, 240, 255, .96) !important;
            --bs-table-hover-bg: rgba(255, 255, 255, .04) !important;
            --bs-table-hover-color: rgba(234, 240, 255, .96) !important;
        }

        .table> :not(caption)>*>* {
            background-color: transparent !important;
            color: rgba(234, 240, 255, .94) !important;
            border-bottom-color: rgba(255, 255, 255, .10) !important;
        }

        .table thead th {
            color: rgba(234, 240, 255, .78) !important;
            font-weight: 800;
            text-transform: none;
        }

        /* Dropdown nativo del select: mejorar opciones (limitado por navegador, pero ayuda) */
        select.form-select option {
            background: #0b1228;
            color: rgba(234, 240, 255, .95);
        }

        .toast-pro {
            border-radius: 18px !important;
            background: rgba(10, 18, 34, .75) !important;
            border: 1px solid rgba(255, 255, 255, .12) !important;
            color: #fff !important;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .35) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body>
    <?php include __DIR__ . "/../app/componentes/Navbar.php"; ?>
    <div class="container py-4">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="h3 mb-0 seccion-titulo">
                    <i class="bi bi-person-vcard me-1"></i>Pensiones
                </div>
                <div class="text-secondary">Gestión pro de pensiones (tablet-first).</div>
            </div>
            <button type="button" id="btn_nueva_pension" class="btn btn-lg btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Nueva pensión
            </button>
        </div>

        <div class="row g-3">
            <!-- Izquierda -->
            <div class="col-12 col-lg-4">
                <div class="card card-pro">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-bold">
                                <i class="bi bi-search me-1"></i>Buscar
                            </div>
                            <span class="badge badge-soft" id="contador_pensiones">0</span>
                        </div>

                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text bg-white border-0">
                                <i class="bi bi-funnel"></i>
                            </span>
                            <input type="text" class="form-control form-control-lg" id="buscador" placeholder="Nombre, placa, plan...">
                        </div>

                        <div class="nav nav-pills flex-column mb-3" role="tablist">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab_listado" type="button">
                                <i class="bi bi-list-check me-2"></i>Listado
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab_formulario" type="button" id="btn_ir_formulario">
                                <i class="bi bi-pencil-square me-2"></i>Alta / Edición
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab_resumen" type="button">
                                <i class="bi bi-graph-up-arrow me-2"></i>Resumen
                            </button>
                        </div>

                        <div class="lista-mini d-grid gap-2" id="lista_pensiones"></div>
                    </div>
                </div>
            </div>

            <!-- Derecha -->
            <div class="col-12 col-lg-8">
                <div class="tab-content">
                    <!-- Listado -->
                    <div class="tab-pane fade show active" id="tab_listado">
                        <div class="card card-pro">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="h5 mb-0 seccion-titulo"><i class="bi bi-table me-2"></i>Listado de pensiones</div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-lg btn-soft" id="btn_exportar_fake">
                                            <i class="bi bi-download me-2"></i>Exportar
                                        </button>
                                        <button class="btn btn-lg btn-soft" id="btn_refrescar">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Refrescar
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table tabla-pro align-middle">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Placa</th>
                                                <th>Plan</th>
                                                <th>Vigencia</th>
                                                <th>Estatus</th>
                                                <th class="text-end">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla_pensiones"></tbody>
                                    </table>
                                </div>

                                <div class="text-secondary small">
                                    <i class="bi bi-info-circle me-1"></i>Selecciona una pensión para editar o registrar pagos.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <div class="tab-pane fade" id="tab_formulario">
                        <div class="card card-pro">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="h5 mb-0 seccion-titulo"><i class="bi bi-ui-checks-grid me-2"></i>Alta / Edición</div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-lg btn-soft" id="btn_limpiar_formulario" type="button">
                                            <i class="bi bi-eraser me-2"></i>Limpiar
                                        </button>
                                        <button class="btn btn-lg btn-soft" id="btn_abrir_modal_pago" type="button" disabled>
                                            <i class="bi bi-arrow-repeat me-2"></i>Renovar pensión
                                        </button>

                                    </div>
                                </div>

                                <form id="form_pensiones">
                                    <input type="hidden" id="pension_id" name="pension_id" value="">

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="cliente_nombre">
                                                    <i class="bi bi-person-fill"></i>Nombre del cliente
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="cliente_nombre" name="cliente_nombre" placeholder="Ej: Juan Pérez">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="cliente_telefono">
                                                    <i class="bi bi-telephone-fill"></i>Teléfono
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="cliente_telefono" name="cliente_telefono" placeholder="Ej: 5512345678">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="vehiculo_placa">
                                                    <i class="bi bi-car-front-fill"></i>Placa
                                                </label>
                                                <input type="text" class="form-control form-control-lg text-uppercase" id="vehiculo_placa" name="vehiculo_placa" placeholder="ABC-123">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="vehiculo_tipo">
                                                    <i class="bi bi-tag-fill"></i>Tipo de vehículo
                                                </label>
                                                <select class="form-select form-select-lg" id="vehiculo_tipo" name="vehiculo_tipo">
                                                    <option value="Automóvil">Automóvil</option>
                                                    <option value="Motocicleta">Motocicleta</option>
                                                    <option value="Camioneta">Camioneta</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="plan_tipo">
                                                    <i class="bi bi-calendar2-week-fill"></i>Plan (libre)
                                                </label>
                                                <input class="form-control form-control-lg"
                                                    id="plan_tipo"
                                                    name="plan_tipo"
                                                    list="sugerencias_plan"
                                                    placeholder="Ej: Mensual, Semanal, Nocturno, 24/7...">
                                                <datalist id="sugerencias_plan">
                                                    <option value="Mensual"></option>
                                                    <option value="Quincenal"></option>
                                                    <option value="Semanal"></option>
                                                    <option value="Diario"></option>
                                                    <option value="Nocturno"></option>
                                                    <option value="24/7"></option>
                                                    <option value="Matutino"></option>
                                                    <option value="Vespertino"></option>
                                                    <option value="30 días"></option>
                                                    <option value="45 días"></option>
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="monto_mxn">
                                                    <i class="bi bi-currency-dollar"></i>Monto (MXN)
                                                </label>
                                                <input type="number" step="0.01" class="form-control form-control-lg" id="monto_mxn" name="monto_mxn" value="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="vigencia_inicio">
                                                    <i class="bi bi-play-circle-fill"></i>Inicio
                                                </label>
                                                <input type="date" class="form-control form-control-lg" id="vigencia_inicio" name="vigencia_inicio">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="vigencia_fin">
                                                    <i class="bi bi-stop-circle-fill"></i>Fin
                                                </label>
                                                <input type="date" class="form-control form-control-lg" id="vigencia_fin" name="vigencia_fin">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="grupo-campo">
                                                <label class="form-label" for="notas">
                                                    <i class="bi bi-journal-text"></i>Notas / Observaciones
                                                </label>
                                                <textarea class="form-control form-control-lg" id="notas" name="notas" placeholder="Ej: Acceso 24/7, tarjeta #A12, condiciones..."></textarea>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-white border" style="border-color: rgba(15,23,42,.08) !important;">
                                                <div>
                                                    <div class="fw-bold"><i class="bi bi-shield-check me-2 text-primary"></i>Estatus</div>
                                                    <div class="text-secondary small">Activa / Suspendida</div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="estatus_activa" name="estatus_activa" checked>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Tarjeta estado de vigencia -->
                                    <div class="tarjeta-vigencia p-3 mt-3" id="tarjeta_vigencia" style="display:none;">
                                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                                            <div>
                                                <div class="fw-bold">
                                                    <i class="bi bi-hourglass-split text-primary me-2"></i>Estado de vigencia
                                                </div>
                                                <div class="detalle-vigencia" id="vigencia_detalle">—</div>
                                            </div>

                                            <div class="d-flex align-items-center gap-3">
                                                <div class="text-end">
                                                    <div class="text-secondary small"><i class="bi bi-calendar-day me-1"></i>Días restantes</div>
                                                    <div class="display-6 kpi mb-0" id="vigencia_dias">—</div>
                                                </div>

                                                <div>
                                                    <span class="badge-estado ok" id="vigencia_badge">
                                                        <i class="bi bi-check-circle"></i>Al corriente
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </form>



                                <!-- ===== Panel Historial de Pagos ===== -->
                                <div class="panel-suave mt-3" id="panel_historial_pagos">
                                    <div class="cabecera">
                                        <div>
                                            <div class="fw-bold"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Historial de pagos</div>
                                            <div class="micro-ayuda" id="texto_historial">Guarda/selecciona una pensión para ver su historial.</div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <span class="badge badge-soft" id="badge_total_pagos"><i class="bi bi-list-ul me-1"></i>0 pagos</span>
                                            <span class="badge text-bg-light border" style="border-color: rgba(15,23,42,.10) !important;" id="badge_total_mxn">
                                                <i class="bi bi-cash-stack me-1"></i>$0
                                            </span>
                                        </div>
                                    </div>
                                    <div class="cuerpo">
                                        <div class="table-responsive">
                                            <table class="table tabla-pro tabla-pagos align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Monto</th>
                                                        <th>Método</th>
                                                        <th>Referencia</th>
                                                        <th class="text-end">Notas</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabla_pagos">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-secondary py-4">
                                                            <i class="bi bi-receipt me-2"></i>Sin pagos para mostrar.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="micro-ayuda mt-2">
                                            <i class="bi bi-info-circle me-1"></i>Al registrar pago, la vigencia se renueva automáticamente (acumulando).
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="tab-pane fade" id="tab_resumen">
                        <div class="card card-pro">
                            <div class="card-body">
                                <div class="h5 mb-2 seccion-titulo"><i class="bi bi-graph-up me-2"></i>Resumen</div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <div class="p-3 rounded-4 bg-white border" style="border-color: rgba(15,23,42,.08) !important;">
                                            <div class="text-secondary small"><i class="bi bi-people me-1"></i>Activas</div>
                                            <div class="display-6 fw-bold" id="kpi_activas">0</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="p-3 rounded-4 bg-white border" style="border-color: rgba(15,23,42,.08) !important;">
                                            <div class="text-secondary small"><i class="bi bi-pause-circle me-1"></i>Suspendidas</div>
                                            <div class="display-6 fw-bold" id="kpi_suspendidas">0</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="p-3 rounded-4 bg-white border" style="border-color: rgba(15,23,42,.08) !important;">
                                            <div class="text-secondary small"><i class="bi bi-cash-coin me-1"></i>Ingresos (estimado)</div>
                                            <div class="display-6 fw-bold" id="kpi_ingresos">$0</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 text-secondary small">
                                    <i class="bi bi-info-circle me-1"></i>El historial real se consulta por pensión al seleccionarla.
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /tab-content -->
            </div>
        </div>
    </div>

    <!-- Barra flotante inferior -->
    <div id="barra_guardado" class="barra-accion">
        <div class="container">
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="text-white">
                    <div class="fw-bold"><i class="bi bi-exclamation-circle me-2"></i>Cambios sin guardar</div>
                    <div class="text-white-50 small">Guarda para aplicar los cambios del formulario.</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-lg btn-outline-light" id="btn_descartar">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Descartar
                    </button>
                    <button type="button" class="btn btn-lg btn-primary" id="btn_guardar">
                        <i class="bi bi-save2 me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Pago -->
    <div class="modal fade" id="modal_pago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="h5 mb-0 fw-bold">
                            <i class="bi bi-arrow-repeat text-primary me-2"></i>Renovar pensión
                        </div>
                        <div class="text-secondary small">
                            Esta acción registra el pago y extiende la vigencia automáticamente.
                        </div>

                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="form_pago" class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="grupo-campo mb-0">
                                <label class="form-label" for="pago_monto">
                                    <i class="bi bi-currency-dollar"></i>Monto (MXN)
                                </label>
                                <input type="number" step="0.01" class="form-control form-control-lg" id="pago_monto" placeholder="Ej: 1200.00">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="grupo-campo mb-0">
                                <label class="form-label" for="pago_metodo">
                                    <i class="bi bi-credit-card-2-front"></i>Método de pago
                                </label>
                                <select class="form-select form-select-lg" id="pago_metodo">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="grupo-campo mb-0">
                                <label class="form-label" for="pago_referencia">
                                    <i class="bi bi-hash"></i>Referencia (opcional)
                                </label>
                                <input type="text" class="form-control form-control-lg" id="pago_referencia" placeholder="Ej: FOLIO-123 / SPEI-XYZ">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="grupo-campo mb-0">
                                <label class="form-label" for="pago_dias_extension">
                                    <i class="bi bi-calendar-plus"></i>Días a extender (opcional)
                                </label>
                                <input type="number" class="form-control form-control-lg" id="pago_dias_extension" placeholder="Ej: 30">
                                <div class="micro-ayuda mt-1">
                                    Si lo dejas vacío, se infiere por el plan (Mensual=30, Semanal=7, etc.).
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="grupo-campo mb-0">
                                <label class="form-label" for="pago_notas">
                                    <i class="bi bi-journal-text"></i>Notas (opcional)
                                </label>
                                <textarea class="form-control form-control-lg" id="pago_notas" placeholder="Observaciones del pago..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-lg btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-lg btn-primary" id="btn_confirmar_pago">
                        <i class="bi bi-arrow-repeat me-2"></i>Renovar ahora
                    </button>

                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <!-- <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="notificacion_toast" class="toast shadow-sm" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-check2-circle text-success me-2"></i>
                <strong class="me-auto">Pensiones</strong>
                <small class="text-secondary">Ahora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body" id="mensaje_toast">Acción realizada.</div>
        </div>
    </div> -->

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

    <script>
        window.URL_BASE = "<?php echo URL_BASE; ?>";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo URL_BASE; ?>publico/js/modulos/pensiones.js"></script>
</body>

</html>