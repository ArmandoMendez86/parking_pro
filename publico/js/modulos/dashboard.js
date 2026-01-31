// Archivo: publico/js/modulos/dashboard.js
/* global Swal */

const estado = {
  kpis: {
    en_estacionamiento: 0,
    prom_estancia_min: 0,
    ingresos_hoy: 0,
    salidas_hoy: 0,
    pensiones_activas: 0,
    pensiones_vencen_7_dias: 0,
    usuarios_activos: 0,
    admins: 0
  },

  form: {
    nombre_negocio: '',
    moneda_simbolo: '$',
    nombre_impresora: 'POS-80'
  },

  original: null,
  ui: {}
};

const URL_BASE = (window.URL_BASE || '');
const RUTA_CONTROLADOR = `${URL_BASE}app/controladores/DashboardControlador.php`;

document.addEventListener('DOMContentLoaded', async () => {
  cacheUI();
  bindEventos();

  await cargarConfig();
  await cargarMetricas();

  estado.original = clonar(estado.form);
  actualizarUIEstadoDirty();
});

function cacheUI() {
  estado.ui = {
    // KPIs
    kpiEnEst: document.getElementById('kpiEnEst'),
    kpiPromEst: document.getElementById('kpiPromEst'),
    kpiIngresosHoy: document.getElementById('kpiIngresosHoy'),
    kpiSalidasHoy: document.getElementById('kpiSalidasHoy'),
    kpiPensionesAct: document.getElementById('kpiPensionesAct'),
    kpiPensionesVencen: document.getElementById('kpiPensionesVencen'),
    kpiUsuariosAct: document.getElementById('kpiUsuariosAct'),
    kpiAdmins: document.getElementById('kpiAdmins'),
    chipEst: document.getElementById('chipEst'),

    // Estado api
    lblApiEstado: document.getElementById('lblApiEstado'),
    lblDatosEstado: document.getElementById('lblDatosEstado'),

    // acciones
    btnRefrescar: document.getElementById('btnRefrescar'),

    // form
    frmAjustes: document.getElementById('frmAjustes'),
    nombreNegocio: document.querySelector('#frmAjustes input[name="nombre_negocio"]'),
    monedaSimbolo: document.querySelector('#frmAjustes input[name="moneda_simbolo"]'),
    nombreImpresora: document.querySelector('#frmAjustes input[name="nombre_impresora"]'),

    // barra sticky
    barra: document.getElementById('stickyActions'),
    btnCancelar: document.getElementById('btnCancelar'),
    btnGuardar: document.getElementById('btnGuardar'),
  };
}

function bindEventos() {
  estado.ui.btnRefrescar?.addEventListener('click', async (e) => {
    e.preventDefault();
    await cargarMetricas();
    toastInfo('Refrescado.');
  });

  const onDirty = () => {
    syncFormDesdeUI();
    actualizarUIEstadoDirty();
    renderKpis(); // actualiza símbolo moneda en el KPI de ingresos
  };

  estado.ui.nombreNegocio?.addEventListener('input', onDirty);
  estado.ui.monedaSimbolo?.addEventListener('input', onDirty);
  estado.ui.nombreImpresora?.addEventListener('input', onDirty);

  estado.ui.btnCancelar?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!estado.original) return;

    estado.form = clonar(estado.original);
    renderForm();
    actualizarUIEstadoDirty();
  });

  estado.ui.btnGuardar?.addEventListener('click', async (e) => {
    e.preventDefault();
    await guardarConfigReal();
  });
}

/* =========================
   API (GET igual estilo, POST como EntradaControlador: FORM-DATA)
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

async function apiPOSTForm(accion, formData) {
  const resp = await fetch(`${RUTA_CONTROLADOR}?accion=${encodeURIComponent(accion)}`, {
    method: 'POST',
    body: formData
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
   CARGAS
========================= */
async function cargarMetricas() {
  setApiState('cargando...', true);

  try {
    const data = await apiGET('metricas');
    const d = data?.datos || {};

    estado.kpis = {
      en_estacionamiento: Number(d.en_estacionamiento ?? 0),
      prom_estancia_min: Number(d.prom_estancia_min ?? 0),
      ingresos_hoy: Number(d.ingresos_hoy ?? 0),
      salidas_hoy: Number(d.salidas_hoy ?? 0),
      pensiones_activas: Number(d.pensiones_activas ?? 0),
      pensiones_vencen_7_dias: Number(d.pensiones_vencen_7_dias ?? 0),
      usuarios_activos: Number(d.usuarios_activos ?? 0),
      admins: Number(d.admins ?? 0)
    };

    renderKpis();
    setApiState('ok', false);
  } catch (err) {
    setApiState('error', false);
    await Swal.fire({ icon: 'error', title: 'Error', text: String(err?.message || err) });
  }
}

async function cargarConfig() {
  setApiState('cargando...', true);

  try {
    const data = await apiGET('config');
    const d = data?.datos || {};

    estado.form = {
      nombre_negocio: String(d.nombre_negocio ?? ''),
      moneda_simbolo: String(d.moneda_simbolo ?? '$'),
      nombre_impresora: String(d.nombre_impresora ?? 'POS-80'),
    };

    renderForm();
    setApiState('ok', false);
  } catch (err) {
    setApiState('error', false);
    await Swal.fire({ icon: 'error', title: 'Error', text: String(err?.message || err) });
  }
}

async function guardarConfigReal() {
  syncFormDesdeUI();

  const errores = validarFormLocal(estado.form);
  if (errores.length) {
    await Swal.fire({
      icon: 'error',
      title: 'Revisa el formulario',
      html: `<div class="text-start">${errores.map(e => `• ${escapeHTML(e)}`).join('<br>')}</div>`,
      confirmButtonText: 'OK'
    });
    return;
  }

  setApiState('guardando...', true);

  try {
    const fd = new FormData();
    fd.append('nombre_negocio', estado.form.nombre_negocio);
    fd.append('moneda_simbolo', estado.form.moneda_simbolo);
    fd.append('nombre_impresora', estado.form.nombre_impresora);

    const data = await apiPOSTForm('config_guardar', fd);

    const d = data?.datos || {};
    estado.form = {
      nombre_negocio: String(d.nombre_negocio ?? estado.form.nombre_negocio),
      moneda_simbolo: String(d.moneda_simbolo ?? estado.form.moneda_simbolo),
      nombre_impresora: String(d.nombre_impresora ?? estado.form.nombre_impresora),
    };

    renderForm();

    estado.original = clonar(estado.form);
    actualizarUIEstadoDirty();

    setApiState('ok', false);

    await Swal.fire({
      icon: 'success',
      title: 'Listo',
      text: data?.mensaje || 'Guardado.',
      confirmButtonText: 'OK'
    });

    await cargarMetricas();
  } catch (err) {
    setApiState('error', false);
    await Swal.fire({ icon: 'error', title: 'Error', text: String(err?.message || err) });
  }
}

/* =========================
   RENDER
========================= */
function renderForm() {
  if (estado.ui.nombreNegocio) estado.ui.nombreNegocio.value = estado.form.nombre_negocio || '';
  if (estado.ui.monedaSimbolo) estado.ui.monedaSimbolo.value = estado.form.moneda_simbolo || '$';
  if (estado.ui.nombreImpresora) estado.ui.nombreImpresora.value = estado.form.nombre_impresora || 'POS-80';
}

function renderKpis() {
  const k = estado.kpis;

  if (estado.ui.kpiEnEst) estado.ui.kpiEnEst.textContent = String(k.en_estacionamiento ?? 0);
  if (estado.ui.kpiPromEst) estado.ui.kpiPromEst.textContent = String(k.prom_estancia_min ?? 0);
  if (estado.ui.kpiIngresosHoy) estado.ui.kpiIngresosHoy.textContent = toMoney(k.ingresos_hoy ?? 0, getSimboloMoneda());
  if (estado.ui.kpiSalidasHoy) estado.ui.kpiSalidasHoy.textContent = String(k.salidas_hoy ?? 0);
  if (estado.ui.kpiPensionesAct) estado.ui.kpiPensionesAct.textContent = String(k.pensiones_activas ?? 0);
  if (estado.ui.kpiPensionesVencen) estado.ui.kpiPensionesVencen.textContent = String(k.pensiones_vencen_7_dias ?? 0);
  if (estado.ui.kpiUsuariosAct) estado.ui.kpiUsuariosAct.textContent = String(k.usuarios_activos ?? 0);
  if (estado.ui.kpiAdmins) estado.ui.kpiAdmins.textContent = String(k.admins ?? 0);

  if (estado.ui.chipEst) {
    const n = Number(k.en_estacionamiento ?? 0);
    const txt = n === 0 ? 'Sin autos' : (n < 10 ? 'Flujo bajo' : (n < 30 ? 'Flujo medio' : 'Flujo alto'));
    estado.ui.chipEst.innerHTML = `<i class="bi bi-activity me-1"></i>${txt}`;
  }
}

/* =========================
   DIRTY CHECKING
========================= */
function syncFormDesdeUI() {
  estado.form.nombre_negocio = (estado.ui.nombreNegocio?.value || '').trim();
  estado.form.moneda_simbolo = (estado.ui.monedaSimbolo?.value || '$').trim();
  estado.form.nombre_impresora = (estado.ui.nombreImpresora?.value || 'POS-80').trim();
}

function isDirty() {
  if (!estado.original) return false;

  const a = normalizarForm(estado.form);
  const b = normalizarForm(estado.original);

  return JSON.stringify(a) !== JSON.stringify(b);
}

function normalizarForm(f) {
  return {
    nombre_negocio: (f.nombre_negocio || '').trim(),
    moneda_simbolo: (f.moneda_simbolo || '$').trim(),
    nombre_impresora: (f.nombre_impresora || '').trim(),
  };
}

function actualizarUIEstadoDirty() {
  const sucio = isDirty();
  if (estado.ui.barra) estado.ui.barra.style.display = sucio ? 'block' : 'none';
}

/* =========================
   UTIL
========================= */
function setApiState(txt, cargando) {
  if (estado.ui.lblApiEstado) estado.ui.lblApiEstado.textContent = txt || '—';
  if (estado.ui.lblDatosEstado) estado.ui.lblDatosEstado.textContent = cargando ? 'sincronizando' : 'db';
}

function getSimboloMoneda() {
  const v = (estado.form.moneda_simbolo || '$').trim();
  return v.length ? v : '$';
}

function toMoney(n, simbolo = '$') {
  const x = Number(n);
  const safe = Number.isFinite(x) ? x : 0;
  return `${simbolo}${safe.toFixed(2)}`;
}

function validarFormLocal(f) {
  const errs = [];
  if (!f.nombre_negocio || f.nombre_negocio.trim().length < 2) errs.push('Nombre del negocio mínimo 2 caracteres.');
  if (!f.moneda_simbolo || f.moneda_simbolo.trim().length < 1) errs.push('Símbolo de moneda es obligatorio.');
  if (f.moneda_simbolo && f.moneda_simbolo.length > 5) errs.push('Símbolo de moneda máx 5 caracteres.');
  if (f.nombre_impresora && f.nombre_impresora.length > 100) errs.push('Impresora máx 100 caracteres.');
  return errs;
}

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
