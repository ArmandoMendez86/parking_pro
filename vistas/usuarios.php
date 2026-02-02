<?php require_once '../config/configuracion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../publico/css/usuarios.css">

</head>

<body>
    <?php include __DIR__ . "/../app/componentes/Navbar.php"; ?>
    <div class="container py-4" style="max-width: 1100px;">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h1 class="h2 titulo-seccion mb-1">
                    <i class="bi bi-people-fill text-primary me-2"></i>Usuarios
                </h1>
                <div class="subtexto">Alta, edición y estado de acceso (tablet-first).</div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-lg btn-outline-secondary" id="btn_refrescar">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refrescar
                </button>
                <button class="btn btn-lg btn-primary" id="btn_nuevo">
                    <i class="bi bi-person-plus-fill me-2"></i>Nuevo
                </button>
            </div>
        </div>

        <div class="layout-grid">

            <!-- LISTA -->
            <div class="card-pro p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="fw-black" style="font-weight:900;">
                        <i class="bi bi-list-task me-2 text-primary"></i>Listado
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <div class="input-group input-group-lg" style="min-width: 260px;">
                            <span class="input-group-text bg-light text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input id="input_buscar" class="form-control" placeholder="Buscar usuario...">
                        </div>

                        <select id="filtro_rol" class="form-select form-select-lg" style="min-width: 190px;">
                            <option value="">Todos los roles</option>
                            <option value="ADMIN">ADMIN</option>
                            <option value="CAJERO">CAJERO</option>
                            <option value="OPERADOR">OPERADOR</option>
                        </select>
                    </div>
                </div>

                <div id="contenedor_lista" class="list-grid"></div>

                <div id="estado_vacio" class="text-center py-5" style="display:none;">
                    <i class="bi bi-person-x display-4 text-secondary"></i>
                    <div class="mt-2 fw-bold text-secondary">Sin usuarios para mostrar</div>
                    <div class="hint mt-1">Cambia filtros o crea uno nuevo.</div>
                </div>
            </div>

            <!-- FORM -->
            <div class="card-pro p-3 p-md-4 form-sticky">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div class="fw-black" style="font-weight:900;">
                        <i class="bi bi-person-gear me-2 text-primary"></i>
                        <span id="titulo_form">Nuevo usuario</span>
                    </div>

                    <span class="chip chip-muted" id="chip_estado_form">
                        <i class="bi bi-circle-fill"></i><span>Sin cambios</span>
                    </span>
                </div>

                <div class="hint mb-3">
                    <i class="bi bi-hand-index-thumb me-1"></i>Campos grandes para táctil. La barra inferior aparece cuando hay cambios.
                </div>

                <form id="form_usuario" autocomplete="off">
                    <input type="hidden" id="usuario_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person-badge me-2"></i>Nombre
                        </label>
                        <input id="nombre" class="form-control form-control-lg" placeholder="Ej. Armando Méndez" maxlength="80">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-at me-2"></i>Usuario (login)
                        </label>
                        <input id="usuario" class="form-control form-control-lg" placeholder="Ej. armando" maxlength="50">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-shield-lock me-2"></i>Rol
                        </label>
                        <select id="rol" class="form-select form-select-lg">
                            <option value="">Seleccionar rol</option>
                            <option value="ADMIN">ADMIN</option>
                            <option value="CAJERO">CAJERO</option>
                            <option value="OPERADOR">OPERADOR</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-key me-2"></i>Contraseña</span>
                            <span class="chip chip-muted">
                                <i class="bi bi-info-circle"></i><span id="txt_password_hint">Obligatoria al crear</span>
                            </span>
                        </label>
                        <input id="password" type="password" class="form-control form-control-lg" placeholder="••••••••" maxlength="60">
                    </div>

                    <div class="card-pro p-3">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-black" style="font-weight:900;">
                                    <i class="bi bi-toggle2-on me-2 text-primary"></i>Usuario activo
                                </div>
                                <div class="hint">Desactiva para bloquear acceso sin borrar.</div>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="activo" style="width:3.25rem;height:1.75rem; cursor:pointer;">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-lg btn-outline-danger flex-grow-1" id="btn_eliminar" disabled>
                            <i class="bi bi-trash3 me-2"></i>Eliminar
                        </button>

                        <button type="button" class="btn btn-lg btn-outline-secondary flex-grow-1" id="btn_limpiar">
                            <i class="bi bi-eraser me-2"></i>Limpiar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Barra Sticky Inferior Inteligente -->
    <div class="barra-accion" id="barra_acciones">
        <div class="container" style="max-width: 1100px;">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-2 text-secondary">
                        <i class="bi bi-exclamation-circle"></i>
                        <div class="small">
                            <span class="fw-bold">Cambios pendientes.</span>
                            <span class="d-none d-md-inline">Guardar o cancelar.</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-auto d-flex gap-2">
                    <button class="btn btn-lg btn-outline-secondary flex-grow-1" id="btn_cancelar">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button class="btn btn-lg btn-primary flex-grow-1" id="btn_guardar">
                        <i class="bi bi-check2-circle me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.URL_BASE = "<?php echo defined('URL_BASE') ? URL_BASE : 'http://localhost/sistema_estacionamiento/'; ?>";
        window.CURRENT_USER_ID = <?php echo (int)($_SESSION['usuario_id'] ?? 0); ?>;
        window.CURRENT_USER_ROLE = "<?php echo (string)($_SESSION['rol'] ?? ''); ?>";
    </script>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="../publico/js/modulos/usuarios.js"></script>
</body>

</html>