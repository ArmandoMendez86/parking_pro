// Archivo: publico/js/modulos/usuarios.js
const estado = {
    lista: [],
    filtro: { q: '', rol: '' },

    form: {
        id: null,
        nombre: '',
        usuario: '',
        rol: '',
        password: '',
        activo: true,
    },

    original: null,
    ui: {}
};

const RUTA_CONTROLADOR = `${window.URL_BASE || ''}app/controladores/UsuariosControlador.php`;

document.addEventListener('DOMContentLoaded', async () => {
    cacheUI();
    bindEventos();
    await cargarLista();
    cargarNuevo();
});

function cacheUI() {
    estado.ui = {
        contenedorLista: document.getElementById('contenedor_lista'),
        estadoVacio: document.getElementById('estado_vacio'),

        inputBuscar: document.getElementById('input_buscar'),
        filtroRol: document.getElementById('filtro_rol'),

        btnNuevo: document.getElementById('btn_nuevo'),
        btnRefrescar: document.getElementById('btn_refrescar'),

        tituloForm: document.getElementById('titulo_form'),
        chipEstadoForm: document.getElementById('chip_estado_form'),
        txtPasswordHint: document.getElementById('txt_password_hint'),

        usuarioId: document.getElementById('usuario_id'),
        nombre: document.getElementById('nombre'),
        usuario: document.getElementById('usuario'),
        rol: document.getElementById('rol'),
        password: document.getElementById('password'),
        activo: document.getElementById('activo'),

        btnEliminar: document.getElementById('btn_eliminar'),
        btnLimpiar: document.getElementById('btn_limpiar'),

        barra: document.getElementById('barra_acciones'),
        btnCancelar: document.getElementById('btn_cancelar'),
        btnGuardar: document.getElementById('btn_guardar'),
    };
}

function bindEventos() {
    estado.ui.inputBuscar.addEventListener('input', async (e) => {
        estado.filtro.q = (e.target.value || '').trim();
        renderLista();
    });

    estado.ui.filtroRol.addEventListener('change', async (e) => {
        estado.filtro.rol = (e.target.value || '').trim();
        renderLista();
    });

    estado.ui.btnNuevo.addEventListener('click', () => cargarNuevo());
    estado.ui.btnRefrescar.addEventListener('click', async () => {
        await cargarLista();
        toastInfo('Refrescado.');
    });

    const onDirty = () => {
        syncFormDesdeUI();
        actualizarUIEstadoDirty();
    };

    estado.ui.nombre.addEventListener('input', onDirty);
    estado.ui.usuario.addEventListener('input', onDirty);
    estado.ui.rol.addEventListener('change', onDirty);
    estado.ui.password.addEventListener('input', onDirty);
    estado.ui.activo.addEventListener('change', onDirty);

    estado.ui.btnLimpiar.addEventListener('click', (e) => {
        e.preventDefault();

        // LIMPIAR = dejar el formulario en blanco (como antes)
        // (Cancelar es el que revierte al snapshot)
        cargarNuevo();

        // también limpia el filtro de búsqueda por si el usuario viene de editar
        // (si no lo quieres, elimina estas 2 líneas)
        // estado.ui.inputBuscar.value = '';
        // estado.filtro.q = '';
    });

    estado.ui.btnEliminar.addEventListener('click', eliminarReal);

    estado.ui.btnCancelar.addEventListener('click', () => {
        if (!estado.original) return cargarNuevo();
        setForm(clonar(estado.original));
        estado.ui.password.value = '';
        estado.form.password = '';
        renderForm();
        actualizarUIEstadoDirty();
    });

    estado.ui.btnGuardar.addEventListener('click', guardarReal);
}

/* =========================
   API
========================= */
async function apiGET(accion, params = {}) {
    const url = new URL(RUTA_CONTROLADOR, window.location.origin);
    url.searchParams.set('accion', accion);
    Object.entries(params).forEach(([k, v]) => {
        if (v !== undefined && v !== null && String(v).trim() !== '') url.searchParams.set(k, v);
    });

    const resp = await fetch(url.toString(), { method: 'GET' });
    const data = await resp.json();
    if (!resp.ok || !data?.exito) throw new Error(data?.mensaje || 'Error');
    return data;
}

async function apiPOST(accion, body = {}) {
    const resp = await fetch(`${RUTA_CONTROLADOR}?accion=${encodeURIComponent(accion)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion, ...body })
    });

    const data = await resp.json();
    if (!resp.ok || !data?.exito) {
        const errs = Array.isArray(data?.errores) ? data.errores : [];
        if (errs.length) throw new Error(errs.join('\n'));
        throw new Error(data?.mensaje || 'Error');
    }
    return data;
}

/* =========================
   LISTA
========================= */
async function cargarLista() {
    const data = await apiGET('listar', { q: '', rol: '' });
    estado.lista = Array.isArray(data?.datos?.usuarios) ? data.datos.usuarios : [];
    renderLista();
}

function renderLista() {
    const cont = estado.ui.contenedorLista;
    const items = filtrarLista(estado.lista, estado.filtro);

    estado.ui.estadoVacio.style.display = items.length ? 'none' : '';
    cont.innerHTML = items.map(u => cardUsuarioHTML(u)).join('');

    items.forEach(u => {
        document.getElementById(`btn_edit_${u.id}`)?.addEventListener('click', () => cargarEditar(u.id));
        document.getElementById(`btn_toggle_${u.id}`)?.addEventListener('click', () => toggleActivoDesdeLista(u.id));
    });
}

function filtrarLista(lista, filtro) {
    const q = (filtro.q || '').trim().toLowerCase();
    const rol = (filtro.rol || '').trim();

    return (lista || []).filter(u => {
        const okRol = !rol || u.rol === rol;
        const texto = `${u.nombre} ${u.usuario} ${u.rol}`.toLowerCase();
        const okQ = !q || texto.includes(q);
        return okRol && okQ;
    });
}

function cardUsuarioHTML(u) {
    const activo = !!Number(u.activo);
    const chipEstado = activo ? 'chip chip-ok' : 'chip chip-off';
    const iconEstado = activo ? 'bi-check-circle-fill' : 'bi-slash-circle-fill';
    const txtEstado = activo ? 'Activo' : 'Inactivo';

    const chipRol = `<span class="chip chip-muted"><i class="bi bi-person-vcard"></i>${escapeHTML(u.rol)}</span>`;
    const chipAcceso = `<span class="chip chip-muted"><i class="bi bi-clock-history"></i>${escapeHTML(formatearAcceso(u.ultimo_acceso))}</span>`;

    return `
    <div class="user-card">
      <div class="top">
        <div class="flex-grow-1">
          <div class="name"><i class="bi bi-person-circle me-2 text-primary"></i>${escapeHTML(u.nombre)}</div>
          <div class="meta">
            <span class="chip"><i class="bi bi-at"></i>${escapeHTML(u.usuario)}</span>
            ${chipRol}
            ${chipAcceso}
            <span class="${chipEstado}"><i class="bi ${iconEstado}"></i>${txtEstado}</span>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-icon btn-outline-secondary" id="btn_toggle_${u.id}" title="Activar/Desactivar">
            <i class="bi bi-toggle2-on fs-4"></i>
          </button>
          <button class="btn btn-icon btn-primary" id="btn_edit_${u.id}" title="Editar">
            <i class="bi bi-pencil-square fs-4"></i>
          </button>
        </div>
      </div>
    </div>
  `;
}

function formatearAcceso(dt) {
    if (!dt) return '—';
    try {
        const d = new Date(String(dt).replace(' ', 'T'));
        if (isNaN(d.getTime())) return String(dt);
        return d.toLocaleString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
    } catch (_) {
        return String(dt);
    }
}

/* =========================
   FORM
========================= */
function cargarNuevo() {
    setForm({ id: null, nombre: '', usuario: '', rol: '', password: '', activo: true });
    estado.original = clonar(estado.form);
    renderForm();
    actualizarUIEstadoDirty();
}

function cargarEditar(id) {
    const u = estado.lista.find(x => Number(x.id) === Number(id));
    if (!u) return;

    setForm({
        id: Number(u.id),
        nombre: u.nombre || '',
        usuario: u.usuario || '',
        rol: u.rol || '',
        password: '',
        activo: !!Number(u.activo)
    });

    estado.original = clonar(estado.form);
    renderForm();
    actualizarUIEstadoDirty();
    scrollToTopForm();
}

function setForm(data) {
    estado.form = {
        id: data?.id ?? null,
        nombre: data?.nombre ?? '',
        usuario: data?.usuario ?? '',
        rol: data?.rol ?? '',
        password: data?.password ?? '',
        activo: !!data?.activo
    };
}

function renderForm() {
    const f = estado.form;

    estado.ui.usuarioId.value = f.id ?? '';
    estado.ui.nombre.value = f.nombre;
    estado.ui.usuario.value = f.usuario;
    estado.ui.rol.value = f.rol;
    estado.ui.password.value = '';
    estado.ui.activo.checked = !!f.activo;

    const esEdicion = !!f.id;
    estado.ui.tituloForm.textContent = esEdicion ? `Editar usuario #${f.id}` : 'Nuevo usuario';
    estado.ui.btnEliminar.disabled = !esEdicion;

    estado.ui.txtPasswordHint.textContent = esEdicion ? 'Dejar vacío para no cambiar' : 'Obligatoria al crear';
}

function syncFormDesdeUI() {
    estado.form.id = estado.ui.usuarioId.value ? Number(estado.ui.usuarioId.value) : null;
    estado.form.nombre = (estado.ui.nombre.value || '').trim();
    estado.form.usuario = (estado.ui.usuario.value || '').trim();
    estado.form.rol = (estado.ui.rol.value || '').trim();
    estado.form.password = (estado.ui.password.value || '');
    estado.form.activo = !!estado.ui.activo.checked;
}

function isDirty() {
    if (!estado.original) return false;

    const a = normalizarForm(estado.form);
    const b = normalizarForm(estado.original);

    const baseDiff = JSON.stringify(a) !== JSON.stringify(b);
    const passDiff = (estado.form.password || '').trim().length > 0; // si escribió contraseña, hay cambios

    return baseDiff || passDiff;
}

function normalizarForm(f) {
    return {
        id: f.id ?? null,
        nombre: (f.nombre || '').trim(),
        usuario: (f.usuario || '').trim(),
        rol: (f.rol || '').trim(),
        activo: !!f.activo,
    };
}

function actualizarUIEstadoDirty() {
    const sucio = isDirty();
    estado.ui.barra.classList.toggle('visible', sucio);

    if (sucio) {
        estado.ui.chipEstadoForm.className = 'chip chip-ok';
        estado.ui.chipEstadoForm.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i><span>Cambios</span>`;
    } else {
        estado.ui.chipEstadoForm.className = 'chip chip-muted';
        estado.ui.chipEstadoForm.innerHTML = `<i class="bi bi-circle-fill"></i><span>Sin cambios</span>`;
    }
}

/* =========================
   ACCIONES REALES
========================= */
async function guardarReal() {
    syncFormDesdeUI();

    const errores = validarFormLocal(estado.form, estado.lista);
    if (errores.length) {
        await Swal.fire({
            icon: 'error',
            title: 'Revisa el formulario',
            html: `<div class="text-start">${errores.map(e => `• ${escapeHTML(e)}`).join('<br>')}</div>`,
            confirmButtonText: 'OK'
        });
        return;
    }

    const payload = {
        id: estado.form.id,
        nombre: estado.form.nombre,
        usuario: estado.form.usuario,
        rol: estado.form.rol,
        activo: estado.form.activo,
    };

    // password solo si escribió
    if ((estado.form.password || '').trim() !== '') {
        payload.password = estado.form.password;
    }

    const data = await apiPOST('guardar', payload);

    await Swal.fire({
        icon: 'success',
        title: 'Listo',
        text: data?.mensaje || 'Guardado.',
        confirmButtonText: 'OK'
    });

    await cargarLista();

    // re-selecciona lo guardado
    const u = data?.datos?.usuario;
    if (u?.id) {
        cargarEditar(u.id);
    } else {
        cargarNuevo();
    }
}

async function eliminarReal() {
    syncFormDesdeUI();
    if (!estado.form.id) return;

    const res = await Swal.fire({
        icon: 'warning',
        title: 'Eliminar usuario',
        text: `Se eliminará el usuario #${estado.form.id}.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    if (!res.isConfirmed) return;

    const data = await apiPOST('eliminar', { id: estado.form.id });

    await Swal.fire({
        icon: 'success',
        title: 'Eliminado',
        text: data?.mensaje || 'OK',
        confirmButtonText: 'OK'
    });

    await cargarLista();
    cargarNuevo();
}

async function toggleActivoDesdeLista(id) {
    const u = estado.lista.find(x => Number(x.id) === Number(id));
    if (!u) return;

    const nuevoActivo = !Number(u.activo);

    await apiPOST('set_activo', { id: Number(id), activo: nuevoActivo });

    await cargarLista();

    // si el mismo usuario está cargado, refresca form
    if (Number(estado.form.id) === Number(id)) {
        const u2 = estado.lista.find(x => Number(x.id) === Number(id));
        if (u2) cargarEditar(u2.id);
    }
}

/* =========================
   VALIDACIÓN LOCAL (rápida)
========================= */
function validarFormLocal(f, lista) {
    const errs = [];
    if (!f.nombre || f.nombre.trim().length < 3) errs.push('Nombre mínimo 3 caracteres.');
    if (!f.usuario || f.usuario.trim().length < 3) errs.push('Usuario mínimo 3 caracteres.');
    if (!f.rol) errs.push('Selecciona un rol.');

    const esNuevo = !f.id;
    if (esNuevo && (!f.password || f.password.length < 4)) errs.push('Contraseña mínima 4 caracteres (al crear).');
    if (!esNuevo && f.password && f.password.length > 0 && f.password.length < 4) errs.push('Contraseña mínima 4 caracteres.');

    const userLower = (f.usuario || '').trim().toLowerCase();
    const dup = (lista || []).some(u => (u.usuario || '').toLowerCase() === userLower && Number(u.id) !== Number(f.id));
    if (dup) errs.push('El usuario (login) ya existe.');

    return errs;
}

/* =========================
   UTIL
========================= */
function clonar(obj) {
    return JSON.parse(JSON.stringify(obj));
}

function escapeHTML(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function toastInfo(msg) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: msg,
        showConfirmButton: false,
        timer: 1400,
        timerProgressBar: true
    });
}

function scrollToTopForm() {
    try {
        estado.ui.tituloForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (_) { }
}
