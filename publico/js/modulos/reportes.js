// publico/js/modulos/reportes.js
// Conectado a backend: app/controladores/ReportesControlador.php (JSON)
// Requiere: window.URL_BASE definido (o '')

const estado = {
  dirty: false,
  modoPeriodo: "rango", // rango | turno
  filtros: {
    tipoReporte: "corte_cajero",

    // rango
    desdeFecha: "",
    hastaFecha: "",
    desdeHora: "00:00",
    hastaHora: "23:59",

    // turno
    turnoFecha: "",
    turnoPreset: "mañana",
    turnoHoraInicio: "08:00",
    turnoHoraFin: "16:00",

    usuario: "",
    metodoPago: ""
  },
  plantilla: { notas: "" },
  cacheUsuarios: []
};

const ENDPOINT = `${window.URL_BASE || ""}/app/controladores/ReportesControlador.php`;

const el = {
  filtroTipoReporte: document.getElementById("filtroTipoReporte"),

  btnModoRango: document.getElementById("btnModoRango"),
  btnModoTurno: document.getElementById("btnModoTurno"),

  bloqueRango: document.getElementById("bloqueRango"),
  filtroDesdeFecha: document.getElementById("filtroDesdeFecha"),
  filtroHastaFecha: document.getElementById("filtroHastaFecha"),
  filtroDesdeHora: document.getElementById("filtroDesdeHora"),
  filtroHastaHora: document.getElementById("filtroHastaHora"),

  bloqueTurno: document.getElementById("bloqueTurno"),
  filtroTurnoFecha: document.getElementById("filtroTurnoFecha"),
  filtroTurnoPreset: document.getElementById("filtroTurnoPreset"),
  bloqueTurnoCustom: document.getElementById("bloqueTurnoCustom"),
  filtroTurnoHoraInicio: document.getElementById("filtroTurnoHoraInicio"),
  filtroTurnoHoraFin: document.getElementById("filtroTurnoHoraFin"),

  filtroUsuario: document.getElementById("filtroUsuario"),
  filtroMetodoPago: document.getElementById("filtroMetodoPago"),

  btnAplicar: document.getElementById("btnAplicar"),
  btnLimpiar: document.getElementById("btnLimpiar"),
  btnExportarCsv: document.getElementById("btnExportarCsv"),
  btnExportarPdf: document.getElementById("btnExportarPdf"),
  btnNuevo: document.getElementById("btnNuevo"),

  badgeTotal: document.getElementById("badgeTotal"),
  tbody: document.getElementById("tbodyReportes"),

  // KPIs (solo caja/corte)
  kpiTotalVendido: document.getElementById("kpiTotalVendido"),
  kpiTotalRecibido: document.getElementById("kpiTotalRecibido"),
  kpiTotalCambio: document.getElementById("kpiTotalCambio"),
  kpiTotalDescuentos: document.getElementById("kpiTotalDescuentos"),
  kpiTotalExtraNoche: document.getElementById("kpiTotalExtraNoche"),
  kpiBoletosPerdidos: document.getElementById("kpiBoletosPerdidos"),
  kpiEfectivo: document.getElementById("kpiEfectivo"),
  kpiTarjeta: document.getElementById("kpiTarjeta"),
  kpiTransferencia: document.getElementById("kpiTransferencia"),
  kpiOtro: document.getElementById("kpiOtro"),

  // tabla head
  theadRow: document.querySelector("table thead tr"),

  // Plantilla
  txtNotas: document.getElementById("txtNotas"),
  sticky: document.getElementById("stickyActions"),
  btnGuardar: document.getElementById("btnGuardar"),
  btnCancelar: document.getElementById("btnCancelar")
};

function setDirty(v) {
  estado.dirty = !!v;
  el.sticky.classList.toggle("show", estado.dirty);
}

function escapeHtml(str) {
  return String(str ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function money(n) {
  return Number(n || 0).toLocaleString("es-MX", { style: "currency", currency: "MXN" });
}

function hoyISO() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
}

function inicioMesISO() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  return `${yyyy}-${mm}-01`;
}

function setModoPeriodo(modo) {
  estado.modoPeriodo = modo;

  el.btnModoRango.classList.toggle("btn-dark", modo === "rango");
  el.btnModoRango.classList.toggle("btn-outline-dark", modo !== "rango");

  el.btnModoTurno.classList.toggle("btn-dark", modo === "turno");
  el.btnModoTurno.classList.toggle("btn-outline-dark", modo !== "turno");

  el.bloqueRango.classList.toggle("d-none", modo !== "rango");
  el.bloqueTurno.classList.toggle("d-none", modo !== "turno");
}

function aplicarPresetTurno(preset) {
  estado.filtros.turnoPreset = preset;

  if (preset === "mañana") {
    estado.filtros.turnoHoraInicio = "08:00";
    estado.filtros.turnoHoraFin = "16:00";
  } else if (preset === "tarde") {
    estado.filtros.turnoHoraInicio = "16:00";
    estado.filtros.turnoHoraFin = "00:00";
  } else if (preset === "noche") {
    estado.filtros.turnoHoraInicio = "00:00";
    estado.filtros.turnoHoraFin = "08:00";
  }

  el.filtroTurnoHoraInicio.value = estado.filtros.turnoHoraInicio;
  el.filtroTurnoHoraFin.value = estado.filtros.turnoHoraFin;

  const isCustom = preset === "custom";
  el.bloqueTurnoCustom.classList.toggle("d-none", !isCustom);
}

function setDefaults() {
  // periodo: rango (mes actual)
  estado.filtros.desdeFecha = inicioMesISO();
  estado.filtros.hastaFecha = hoyISO();
  estado.filtros.desdeHora = "00:00";
  estado.filtros.hastaHora = "23:59";

  el.filtroDesdeFecha.value = estado.filtros.desdeFecha;
  el.filtroHastaFecha.value = estado.filtros.hastaFecha;
  el.filtroDesdeHora.value = estado.filtros.desdeHora;
  el.filtroHastaHora.value = estado.filtros.hastaHora;

  // turno
  estado.filtros.turnoFecha = hoyISO();
  el.filtroTurnoFecha.value = estado.filtros.turnoFecha;

  el.filtroTurnoPreset.value = "mañana";
  aplicarPresetTurno("mañana");

  // otros
  estado.filtros.tipoReporte = "corte_cajero";
  el.filtroTipoReporte.value = estado.filtros.tipoReporte;

  estado.filtros.usuario = "";
  estado.filtros.metodoPago = "";
  el.filtroUsuario.value = "";
  el.filtroMetodoPago.value = "";

  // plantilla
  estado.plantilla.notas = "";
  el.txtNotas.value = "";

  setModoPeriodo("rango");
  setDirty(false);
}

function leerFiltros() {
  estado.filtros.tipoReporte = el.filtroTipoReporte.value;

  // rango
  estado.filtros.desdeFecha = el.filtroDesdeFecha.value;
  estado.filtros.hastaFecha = el.filtroHastaFecha.value;
  estado.filtros.desdeHora = el.filtroDesdeHora.value || "00:00";
  estado.filtros.hastaHora = el.filtroHastaHora.value || "23:59";

  // turno
  estado.filtros.turnoFecha = el.filtroTurnoFecha.value;
  estado.filtros.turnoPreset = el.filtroTurnoPreset.value;
  estado.filtros.turnoHoraInicio = el.filtroTurnoHoraInicio.value || estado.filtros.turnoHoraInicio;
  estado.filtros.turnoHoraFin = el.filtroTurnoHoraFin.value || estado.filtros.turnoHoraFin;

  // otros
  estado.filtros.usuario = el.filtroUsuario.value;
  estado.filtros.metodoPago = el.filtroMetodoPago.value;

  // plantilla
  estado.plantilla.notas = el.txtNotas.value || "";
}

function buildQueryParams() {
  const p = new URLSearchParams();

  p.set("modo_periodo", estado.modoPeriodo);

  // corte/rango/turno
  if (estado.modoPeriodo === "rango") {
    p.set("desde_fecha", estado.filtros.desdeFecha);
    p.set("hasta_fecha", estado.filtros.hastaFecha);
    p.set("desde_hora", estado.filtros.desdeHora);
    p.set("hasta_hora", estado.filtros.hastaHora);
  } else {
    p.set("turno_fecha", estado.filtros.turnoFecha);
    p.set("turno_hora_inicio", estado.filtros.turnoHoraInicio);
    p.set("turno_hora_fin", estado.filtros.turnoHoraFin);
  }

  if (estado.filtros.usuario) p.set("usuario", estado.filtros.usuario);
  if (estado.filtros.metodoPago) p.set("metodo_pago", estado.filtros.metodoPago);

  return p;
}

async function apiGet(action, params) {
  const url = `${ENDPOINT}?accion=${encodeURIComponent(action)}&${params.toString()}`;
  const res = await fetch(url, { method: "GET" });
  const data = await res.json().catch(() => null);

  if (!data || data.ok !== true) {
    const msg = (data && data.mensaje) ? data.mensaje : "Error de comunicación con el servidor";
    throw new Error(msg);
  }
  return data.datos || {};
}

function setTableHead(cols) {
  // cols: [{label, alignEnd?}]
  el.theadRow.innerHTML = cols.map(c => {
    const cls = c.alignEnd ? "text-end" : "";
    return `<th class="${cls}">${escapeHtml(c.label)}</th>`;
  }).join("");
}

function setBadgeTotal(n) {
  el.badgeTotal.innerHTML = `<i class="bi bi-collection me-1"></i>${Number(n || 0)}`;
}

function setKPIsBlank() {
  if (!el.kpiTotalVendido) return;

  el.kpiTotalVendido.textContent = money(0);
  el.kpiTotalRecibido.textContent = money(0);
  el.kpiTotalCambio.textContent = money(0);
  el.kpiTotalDescuentos.textContent = money(0);
  el.kpiTotalExtraNoche.textContent = money(0);
  el.kpiBoletosPerdidos.textContent = "0";

  el.kpiEfectivo.textContent = money(0);
  el.kpiTarjeta.textContent = money(0);
  el.kpiTransferencia.textContent = money(0);
  el.kpiOtro.textContent = money(0);
}

function renderKPIsCorte(resumen, porMetodo) {
  const r = resumen || {};
  const pm = porMetodo || {};

  el.kpiTotalVendido.textContent = money(r.total_vendido);
  el.kpiTotalRecibido.textContent = money(r.total_recibido);
  el.kpiTotalCambio.textContent = money(r.total_cambio);
  el.kpiTotalDescuentos.textContent = money(r.total_descuentos);
  el.kpiTotalExtraNoche.textContent = money(r.total_extra_noche);
  el.kpiBoletosPerdidos.textContent = String(r.boletos_perdidos ?? 0);

  el.kpiEfectivo.textContent = money(pm.Efectivo || 0);
  el.kpiTarjeta.textContent = money(pm.Tarjeta || 0);
  el.kpiTransferencia.textContent = money(pm.Transferencia || 0);
  el.kpiOtro.textContent = money(pm.Otro || 0);
}

function renderRowsCorteDetalle(rows) {
  setTableHead([
    { label: "Fecha" },
    { label: "Placa" },
    { label: "Cajero" },
    { label: "Método" },
    { label: "Total", alignEnd: true },
    { label: "Acciones", alignEnd: true }
  ]);

  const safeRows = Array.isArray(rows) ? rows : [];
  setBadgeTotal(safeRows.length);

  el.tbody.innerHTML = safeRows.map((r, i) => {
    const badgeBoleto = Number(r.boleto_perdido) === 1
      ? `<span class="badge text-bg-danger ms-2">Boleto perdido</span>`
      : ``;

    const ref = r.referencia_pago ? `<div class="small text-muted">${escapeHtml(r.referencia_pago)}</div>` : "";

    return `
      <tr>
        <td class="mono">
          ${escapeHtml(r.fecha_salida || "")}
          ${badgeBoleto}
        </td>
        <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
        <td>${escapeHtml(r.usuario_cobro || "")}</td>
        <td>
          <span class="badge text-bg-light border text-dark">${escapeHtml(r.metodo_pago || "")}</span>
          ${ref}
        </td>
        <td class="text-end mono">${money(r.monto_total)}</td>
        <td class="text-end">
          <button class="btn btn-outline-dark btn-lg me-2" data-accion="ver_corte" data-index="${i}">
            <i class="bi bi-eye"></i>
          </button>
        </td>
      </tr>
    `;
  }).join("");

  // guardamos dataset para modal/alert
  el.tbody.dataset.lastAction = "corte";
  el.tbody.dataset.lastRows = JSON.stringify(safeRows);
}

function renderRowsCorteDiario(dias) {
  setKPIsBlank();

  setTableHead([
    { label: "Día" },
    { label: "Salidas", alignEnd: true },
    { label: "Total", alignEnd: true },
    { label: "Descuentos", alignEnd: true },
    { label: "Extra noche", alignEnd: true },
    { label: "Min prom", alignEnd: true }
  ]);

  const rows = Array.isArray(dias) ? dias : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.dia || "")}</td>
      <td class="text-end mono">${Number(r.salidas || 0)}</td>
      <td class="text-end mono">${money(r.total_vendido)}</td>
      <td class="text-end mono">${money(r.total_descuentos)}</td>
      <td class="text-end mono">${money(r.total_extra_noche)}</td>
      <td class="text-end mono">${Number(r.minutos_promedio || 0).toFixed(0)}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "corte_diario";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsDescuentos(detalle, resumen) {
  setKPIsBlank();
  // reutilizamos KPI “Descuentos” como resumen visible
  el.kpiTotalDescuentos.textContent = money(resumen?.total_descuentos || 0);

  setTableHead([
    { label: "Fecha" },
    { label: "Placa" },
    { label: "Cajero" },
    { label: "Tipo" },
    { label: "Monto", alignEnd: true },
    { label: "Motivo" }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.fecha_salida || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
      <td>${escapeHtml(r.usuario_cobro || "")}</td>
      <td>${escapeHtml(r.descuento_tipo || "")}</td>
      <td class="text-end mono">${money(r.descuento_monto)}</td>
      <td>${escapeHtml(r.descuento_motivo || "")}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "descuentos";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsBoletosPerdidos(detalle, resumen) {
  setKPIsBlank();
  el.kpiBoletosPerdidos.textContent = String(resumen?.movimientos ?? 0);

  setTableHead([
    { label: "Fecha" },
    { label: "Placa" },
    { label: "Cajero" },
    { label: "Método" },
    { label: "Total", alignEnd: true }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.fecha_salida || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
      <td>${escapeHtml(r.usuario_cobro || "")}</td>
      <td><span class="badge text-bg-light border text-dark">${escapeHtml(r.metodo_pago || "")}</span></td>
      <td class="text-end mono">${money(r.monto_total)}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "boletos_perdidos";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsExtraNoche(detalle, resumen) {
  setKPIsBlank();
  el.kpiTotalExtraNoche.textContent = money(resumen?.total_extra_noche || 0);

  setTableHead([
    { label: "Fecha" },
    { label: "Placa" },
    { label: "Cajero" },
    { label: "Método" },
    { label: "Extra", alignEnd: true },
    { label: "Total", alignEnd: true }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.fecha_salida || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
      <td>${escapeHtml(r.usuario_cobro || "")}</td>
      <td><span class="badge text-bg-light border text-dark">${escapeHtml(r.metodo_pago || "")}</span></td>
      <td class="text-end mono">${money(r.extra_noche)}</td>
      <td class="text-end mono">${money(r.monto_total)}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "extra_noche";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsAnticipos(detalle, resumen) {
  setKPIsBlank();

  setTableHead([
    { label: "Fecha anticipo" },
    { label: "Placa" },
    { label: "Usuario" },
    { label: "Concepto" },
    { label: "Monto", alignEnd: true }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.pago_adelantado_fecha || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
      <td>${escapeHtml(r.pago_adelantado_usuario || "")}</td>
      <td>${escapeHtml(r.pago_adelantado_concepto || "")}</td>
      <td class="text-end mono">${money(r.pago_adelantado_monto)}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "anticipos";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsOcupacion(detalle, resumen) {
  setKPIsBlank();

  setTableHead([
    { label: "Ingreso" },
    { label: "Placa" },
    { label: "Tipo" },
    { label: "Ingreso" },
    { label: "Operador" },
    { label: "Anticipo", alignEnd: true }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.ingreso_id || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
      <td>${escapeHtml(r.tipo_vehiculo || "")}</td>
      <td class="mono">${escapeHtml(r.fecha_ingreso || "")}</td>
      <td>${escapeHtml(r.usuario_registro || "")}</td>
      <td class="text-end mono">${money(r.pago_adelantado_monto)}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "ocupacion";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsEntradasPeriodo(detalle, porDia) {
  setKPIsBlank();

  // Por simplicidad en una sola tabla: mostramos detalle (es lo más útil)
  // Si quieres, luego hacemos tabs: "Resumen por día" / "Detalle"
  setTableHead([
    { label: "Ingreso" },
    { label: "Fecha" },
    { label: "Placa" },
    { label: "Tipo" },
    { label: "Estado" },
    { label: "Operador" }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.ingreso_id || "")}</td>
      <td class="mono">${escapeHtml(r.fecha_ingreso || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
      <td>${escapeHtml(r.tipo_vehiculo || "")}</td>
      <td>${escapeHtml(r.estado || "")}</td>
      <td>${escapeHtml(r.usuario_registro || "")}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "entradas_periodo";
  el.tbody.dataset.lastRows = JSON.stringify({ detalle: rows, porDia: porDia || [] });
}

function renderRowsEstanciaPromedio(resumen, topLargas) {
  setKPIsBlank();

  // usamos KPI "Total vendido" como “Salidas” en este reporte (solo visual)
  el.kpiTotalVendido.textContent = String(resumen?.salidas ?? 0);
  el.kpiTotalRecibido.textContent = `${Number(resumen?.minutos_promedio || 0).toFixed(0)} min`;

  setTableHead([
    { label: "Fecha salida" },
    { label: "Placa" },
    { label: "Tipo" },
    { label: "Cajero" },
    { label: "Minutos", alignEnd: true },
    { label: "Total", alignEnd: true }
  ]);

  const rows = Array.isArray(topLargas) ? topLargas : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.fecha_salida || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.placa || "")}</td>
      <td>${escapeHtml(r.tipo_vehiculo || "")}</td>
      <td>${escapeHtml(r.usuario_cobro || "")}</td>
      <td class="text-end mono">${Number(r.minutos_totales || 0)}</td>
      <td class="text-end mono">${money(r.monto_total)}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "estancia_promedio";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsPensionesActivas(detalle) {
  setKPIsBlank();

  setTableHead([
    { label: "Cliente" },
    { label: "Teléfono" },
    { label: "Placa" },
    { label: "Plan" },
    { label: "Monto", alignEnd: true },
    { label: "Vigencia fin" }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="fw-semibold">${escapeHtml(r.cliente_nombre || "")}</td>
      <td>${escapeHtml(r.cliente_telefono || "")}</td>
      <td class="mono">${escapeHtml(r.vehiculo_placa || "")}</td>
      <td>${escapeHtml(r.plan_nombre || "")}</td>
      <td class="text-end mono">${money(r.monto_mxn)}</td>
      <td class="mono">${escapeHtml(r.vigencia_fin || "")}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "pensiones_activas";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsPensionesVencer(detalle) {
  setKPIsBlank();

  setTableHead([
    { label: "Cliente" },
    { label: "Placa" },
    { label: "Plan" },
    { label: "Vence" },
    { label: "Días", alignEnd: true },
    { label: "Monto", alignEnd: true }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="fw-semibold">${escapeHtml(r.cliente_nombre || "")}</td>
      <td class="mono">${escapeHtml(r.vehiculo_placa || "")}</td>
      <td>${escapeHtml(r.plan_nombre || "")}</td>
      <td class="mono">${escapeHtml(r.vigencia_fin || "")}</td>
      <td class="text-end mono">${Number(r.dias_restantes || 0)}</td>
      <td class="text-end mono">${money(r.monto_mxn)}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "pensiones_vencer";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

function renderRowsPagosPensiones(detalle, resumen) {
  setKPIsBlank();
  el.kpiTotalVendido.textContent = money(resumen?.total_cobrado || 0);

  setTableHead([
    { label: "Fecha pago" },
    { label: "Cliente" },
    { label: "Placa" },
    { label: "Método" },
    { label: "Monto", alignEnd: true },
    { label: "Usuario" }
  ]);

  const rows = Array.isArray(detalle) ? detalle : [];
  setBadgeTotal(rows.length);

  el.tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="mono">${escapeHtml(r.fecha_pago || "")}</td>
      <td class="fw-semibold">${escapeHtml(r.cliente_nombre || "")}</td>
      <td class="mono">${escapeHtml(r.vehiculo_placa || "")}</td>
      <td><span class="badge text-bg-light border text-dark">${escapeHtml(r.metodo_pago || "")}</span></td>
      <td class="text-end mono">${money(r.monto_mxn)}</td>
      <td>${escapeHtml(r.usuario || "")}</td>
    </tr>
  `).join("");

  el.tbody.dataset.lastAction = "pagos_pensiones";
  el.tbody.dataset.lastRows = JSON.stringify(rows);
}

async function cargarUsuarios() {
  try {
    const datos = await apiGet("obtener_usuarios", new URLSearchParams());
    const usuarios = Array.isArray(datos.usuarios) ? datos.usuarios : [];
    estado.cacheUsuarios = usuarios;

    // repinta dropdown
    const actual = el.filtroUsuario.value || "";
    el.filtroUsuario.innerHTML = `<option value="">Todos</option>` + usuarios.map(u => {
      const val = escapeHtml(u.usuario || "");
      const label = escapeHtml(`${u.nombre || u.usuario} (${u.rol || ""})`);
      return `<option value="${val}">${label}</option>`;
    }).join("");

    el.filtroUsuario.value = actual;
  } catch (e) {
    // si falla, dejamos los options actuales
  }
}

async function aplicarReporte() {
  leerFiltros();
  setDirty(false);

  const tipo = estado.filtros.tipoReporte;
  const p = buildQueryParams();

  // Algunos reportes no usan turno (pero el backend lo ignora o usa rango simple)
  try {
    // UX: estado “cargando”
    el.btnAplicar.disabled = true;
    el.btnAplicar.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cargando`;

    if (tipo === "corte_cajero") {
      const datos = await apiGet("corte_cajero", p);
      renderKPIsCorte(datos.resumen, datos.por_metodo);
      renderRowsCorteDetalle(datos.detalle);
      return;
    }

    if (tipo === "corte_diario") {
      const datos = await apiGet("corte_diario", p);
      renderRowsCorteDiario(datos.dias);
      return;
    }

    if (tipo === "descuentos") {
      // opcionales: descuento_tipo, min_descuento (si luego agregas UI)
      const datos = await apiGet("descuentos", p);
      renderRowsDescuentos(datos.detalle, datos.resumen);
      return;
    }

    if (tipo === "boletos_perdidos") {
      const datos = await apiGet("boletos_perdidos", p);
      renderRowsBoletosPerdidos(datos.detalle, datos.resumen);
      return;
    }

    if (tipo === "extra_noche") {
      const datos = await apiGet("extra_noche", p);
      renderRowsExtraNoche(datos.detalle, datos.resumen);
      return;
    }

    if (tipo === "anticipos") {
      // anticipos usa rango por fecha (desde_fecha/hasta_fecha) -> ya lo mandamos si modo=rango
      // Si está en turno, el backend lo ignorará, mejor forzamos rango aquí
      if (estado.modoPeriodo !== "rango") {
        setModoPeriodo("rango");
        leerFiltros();
      }
      const p2 = buildQueryParams();
      const datos = await apiGet("anticipos", p2);
      renderRowsAnticipos(datos.detalle, datos.resumen);
      return;
    }

    if (tipo === "ocupacion") {
      // filtros extra (placa/id_tarifa) no están en UI aún, backend acepta vacíos
      const datos = await apiGet("ocupacion", new URLSearchParams());
      renderRowsOcupacion(datos.detalle, datos.resumen);
      return;
    }

    if (tipo === "entradas_periodo") {
      // requiere rango (fecha). si estabas en turno, lo pasamos igual con modo/rango simple en backend
      if (estado.modoPeriodo !== "rango") {
        setModoPeriodo("rango");
        leerFiltros();
      }
      const p2 = buildQueryParams();
      const datos = await apiGet("entradas_periodo", p2);
      renderRowsEntradasPeriodo(datos.detalle, datos.por_dia);
      return;
    }

    if (tipo === "estancia_promedio") {
      const datos = await apiGet("estancia_promedio", p);
      renderRowsEstanciaPromedio(datos.resumen, datos.top_largas);
      return;
    }

    if (tipo === "pensiones_activas") {
      // UI no tiene buscador "q" aún; backend acepta q vacío
      const datos = await apiGet("pensiones_activas", new URLSearchParams());
      renderRowsPensionesActivas(datos.detalle);
      return;
    }

    if (tipo === "pensiones_vencer") {
      // UI no tiene selector dias aún; por defecto 7
      const params = new URLSearchParams();
      params.set("dias", "7");
      const datos = await apiGet("pensiones_vencer", params);
      renderRowsPensionesVencer(datos.detalle);
      return;
    }

    if (tipo === "pagos_pensiones") {
      // pagos_pensiones usa rango (desde_fecha/hasta_fecha). si turno -> forzamos rango
      if (estado.modoPeriodo !== "rango") {
        setModoPeriodo("rango");
        leerFiltros();
      }
      const p2 = buildQueryParams();
      const datos = await apiGet("pagos_pensiones", p2);
      renderRowsPagosPensiones(datos.detalle, datos.resumen);
      return;
    }

    throw new Error("Tipo de reporte no soportado en UI");
  } catch (e) {
    setKPIsBlank();
    setTableHead([{ label: "Error" }]);
    el.tbody.innerHTML = `
      <tr>
        <td class="text-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>${escapeHtml(e.message || "Error")}
        </td>
      </tr>
    `;
    setBadgeTotal(0);
  } finally {
    el.btnAplicar.disabled = false;
    el.btnAplicar.innerHTML = `<i class="bi bi-search me-2"></i>Aplicar`;
  }
}

function exportarCSVDesdeTabla() {
  const rows = Array.from(el.tbody.querySelectorAll("tr")).map(tr =>
    Array.from(tr.querySelectorAll("td")).map(td => td.innerText.replace(/\s+/g, " ").trim())
  );
  if (!rows.length) return;

  // headers
  const headers = Array.from(el.theadRow.querySelectorAll("th")).map(th => th.innerText.trim());
  const all = [headers, ...rows];

  const csv = all.map(r =>
    r.map(v => `"${String(v).replaceAll('"', '""')}"`).join(",")
  ).join("\n");

  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `reporte_${estado.filtros.tipoReporte}_${Date.now()}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function wirePeriodMode() {
  el.btnModoRango.addEventListener("click", () => {
    setModoPeriodo("rango");
    setDirty(true);
  });

  el.btnModoTurno.addEventListener("click", () => {
    setModoPeriodo("turno");
    setDirty(true);
  });

  el.filtroTurnoPreset.addEventListener("change", () => {
    aplicarPresetTurno(el.filtroTurnoPreset.value);
    setDirty(true);
  });
}

function wireDirty() {
  const campos = [
    el.filtroTipoReporte,
    el.filtroDesdeFecha, el.filtroHastaFecha, el.filtroDesdeHora, el.filtroHastaHora,
    el.filtroTurnoFecha, el.filtroTurnoPreset, el.filtroTurnoHoraInicio, el.filtroTurnoHoraFin,
    el.filtroUsuario, el.filtroMetodoPago,
    el.txtNotas
  ];

  campos.forEach(c => {
    c.addEventListener("input", () => setDirty(true));
    c.addEventListener("change", () => setDirty(true));
  });
}

function wireEventos() {
  el.btnAplicar.addEventListener("click", aplicarReporte);

  el.btnLimpiar.addEventListener("click", async () => {
    setDefaults();
    await aplicarReporte();
  });

  el.btnExportarCsv.addEventListener("click", exportarCSVDesdeTabla);

  el.btnExportarPdf.addEventListener("click", () => {
    alert("PDF: si quieres, lo implementamos en backend (generar PDF con TCPDF/FPDF o imprimir vista).");
  });

  el.btnNuevo.addEventListener("click", () => {
    el.txtNotas.value = "";
    estado.plantilla.notas = "";
    setDirty(false);
  });

  el.btnCancelar.addEventListener("click", () => setDirty(false));

  el.btnGuardar.addEventListener("click", () => {
    estado.plantilla.notas = el.txtNotas.value || "";
    setDirty(false);
    alert("Notas guardadas localmente (si quieres persistirlas en BD, creamos tabla de plantillas).");
  });

  el.tbody.addEventListener("click", (e) => {
    const btn = e.target.closest("button[data-accion]");
    if (!btn) return;

    const accion = btn.getAttribute("data-accion");
    const idx = Number(btn.getAttribute("data-index"));

    if (accion === "ver_corte") {
      const raw = el.tbody.dataset.lastRows || "[]";
      const rows = JSON.parse(raw);
      const r = rows[idx];
      if (!r) return;

      const msg = [
        `Fecha: ${r.fecha_salida}`,
        `Placa: ${r.placa}`,
        `Tipo: ${r.tipo_vehiculo || ""}`,
        `Cajero: ${r.usuario_cobro}`,
        `Método: ${r.metodo_pago}`,
        r.referencia_pago ? `Referencia: ${r.referencia_pago}` : null,
        `Minutos: ${r.minutos_totales ?? ""}`,
        `Total: ${money(r.monto_total)}`,
        `Descuento: ${money(r.descuento_monto)}`,
        `Extra noche: ${money(r.extra_noche)}`,
        `Recibido: ${money(r.monto_recibido)}`,
        `Cambio: ${money(r.monto_cambio)}`,
        `Boleto perdido: ${Number(r.boleto_perdido) === 1 ? "Sí" : "No"}`
      ].filter(Boolean).join("\n");

      alert(msg);
    }
  });

  // si cambias tipo de reporte, aplicamos automático (más cómodo en tablet)
  el.filtroTipoReporte.addEventListener("change", () => {
    // para reportes que dependen del periodo, mantenemos filtros y aplicamos
    aplicarReporte();
  });
}

(async function init() {
  setDefaults();
  wirePeriodMode();
  wireDirty();
  wireEventos();

  await cargarUsuarios();
  await aplicarReporte();
})();
