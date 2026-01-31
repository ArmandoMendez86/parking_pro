// publico/js/modulos/reportes.js

const estado = {
  modo: "nuevo", // nuevo | editar
  dirty: false,
  filtros: {
    tipo: "ventas",
    desde: "",
    hasta: ""
  },
  formulario: {
    folio: "",
    tipo: "ventas",
    estado: "borrador",
    desde: "",
    hasta: "",
    notas: ""
  },
  listado: []
};

const el = {
  filtroTipo: document.getElementById("filtroTipo"),
  filtroDesde: document.getElementById("filtroDesde"),
  filtroHasta: document.getElementById("filtroHasta"),
  btnAplicar: document.getElementById("btnAplicar"),
  btnLimpiar: document.getElementById("btnLimpiar"),
  btnExportarCsv: document.getElementById("btnExportarCsv"),
  btnExportarPdf: document.getElementById("btnExportarPdf"),

  btnNuevo: document.getElementById("btnNuevo"),
  badgeTotal: document.getElementById("badgeTotal"),
  tbody: document.getElementById("tbodyReportes"),

  estadoModo: document.getElementById("estadoModo"),
  txtFolio: document.getElementById("txtFolio"),
  txtTipo: document.getElementById("txtTipo"),
  txtEstado: document.getElementById("txtEstado"),
  txtDesde: document.getElementById("txtDesde"),
  txtHasta: document.getElementById("txtHasta"),
  txtNotas: document.getElementById("txtNotas"),

  sticky: document.getElementById("stickyActions"),
  btnGuardar: document.getElementById("btnGuardar"),
  btnCancelar: document.getElementById("btnCancelar")
};

function setDirty(valor) {
  estado.dirty = !!valor;
  el.sticky.classList.toggle("show", estado.dirty);
}

function setModo(modo) {
  estado.modo = modo;
  el.estadoModo.innerHTML = modo === "editar"
    ? `<i class="bi bi-pencil-square me-1"></i>Editando`
    : `<i class="bi bi-plus-circle me-1"></i>Nuevo`;
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

function setFechasDefault() {
  const desde = inicioMesISO();
  const hasta = hoyISO();

  el.filtroDesde.value = desde;
  el.filtroHasta.value = hasta;

  el.txtDesde.value = desde;
  el.txtHasta.value = hasta;

  estado.filtros.desde = desde;
  estado.filtros.hasta = hasta;

  estado.formulario.desde = desde;
  estado.formulario.hasta = hasta;
}

function cargarDemo() {
  estado.listado = [
    { folio: "RPT-00012", tipo: "ventas", rango: "2026-01-01 a 2026-01-31", total: 12890.50, estado: "generado" },
    { folio: "RPT-00011", tipo: "inventario", rango: "2026-01-01 a 2026-01-15", total: 0, estado: "borrador" },
    { folio: "RPT-00010", tipo: "clientes", rango: "2025-12-01 a 2025-12-31", total: 0, estado: "enviado" }
  ];
  renderListado();
}

function renderListado() {
  el.tbody.innerHTML = estado.listado.map((r, i) => {
    const badge = r.estado === "generado"
      ? `<span class="badge text-bg-success">Generado</span>`
      : r.estado === "enviado"
        ? `<span class="badge text-bg-primary">Enviado</span>`
        : `<span class="badge text-bg-secondary">Borrador</span>`;

    const total = Number(r.total || 0).toLocaleString("es-MX", { style: "currency", currency: "MXN" });

    return `
      <tr>
        <td class="fw-semibold">${escapeHtml(r.folio)}</td>
        <td class="text-capitalize">${escapeHtml(r.tipo)}</td>
        <td>${escapeHtml(r.rango)}<div class="small mt-1">${badge}</div></td>
        <td class="text-end">${total}</td>
        <td class="text-end">
          <button class="btn btn-outline-dark btn-lg me-2" data-accion="editar" data-index="${i}">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-outline-danger btn-lg" data-accion="eliminar" data-index="${i}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join("");

  el.badgeTotal.innerHTML = `<i class="bi bi-collection me-1"></i>${estado.listado.length}`;
}

function escapeHtml(str) {
  return String(str ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function leerFiltros() {
  estado.filtros.tipo = el.filtroTipo.value;
  estado.filtros.desde = el.filtroDesde.value;
  estado.filtros.hasta = el.filtroHasta.value;
}

function aplicarFiltros() {
  leerFiltros();
  // Demo: filtra sobre estado.listado original (en real vendrá del backend)
  const tipo = estado.filtros.tipo;

  const filtrado = [
    { folio: "RPT-00012", tipo: "ventas", rango: "2026-01-01 a 2026-01-31", total: 12890.50, estado: "generado" },
    { folio: "RPT-00011", tipo: "inventario", rango: "2026-01-01 a 2026-01-15", total: 0, estado: "borrador" },
    { folio: "RPT-00010", tipo: "clientes", rango: "2025-12-01 a 2025-12-31", total: 0, estado: "enviado" }
  ].filter(r => r.tipo === tipo);

  estado.listado = filtrado;
  renderListado();
}

function limpiarFiltros() {
  el.filtroTipo.value = "ventas";
  setFechasDefault();
  aplicarFiltros();
}

function setFormulario(data) {
  estado.formulario = { ...estado.formulario, ...data };

  el.txtFolio.value = estado.formulario.folio || "";
  el.txtTipo.value = estado.formulario.tipo || "ventas";
  el.txtEstado.value = estado.formulario.estado || "borrador";
  el.txtDesde.value = estado.formulario.desde || "";
  el.txtHasta.value = estado.formulario.hasta || "";
  el.txtNotas.value = estado.formulario.notas || "";
}

function leerFormulario() {
  estado.formulario.tipo = el.txtTipo.value;
  estado.formulario.estado = el.txtEstado.value;
  estado.formulario.desde = el.txtDesde.value;
  estado.formulario.hasta = el.txtHasta.value;
  estado.formulario.notas = el.txtNotas.value;
}

function resetFormulario() {
  setModo("nuevo");
  setFormulario({
    folio: "",
    tipo: "ventas",
    estado: "borrador",
    desde: estado.filtros.desde,
    hasta: estado.filtros.hasta,
    notas: ""
  });
  setDirty(false);
}

function onEditar(index) {
  const r = estado.listado[index];
  setModo("editar");
  setFormulario({
    folio: r.folio,
    tipo: r.tipo,
    estado: r.estado,
    desde: r.rango.split(" a ")[0] || estado.filtros.desde,
    hasta: r.rango.split(" a ")[1] || estado.filtros.hasta,
    notas: ""
  });
  setDirty(false);
}

function onEliminar(index) {
  // Demo: solo remueve del arreglo
  estado.listado.splice(index, 1);
  renderListado();
  if (estado.modo === "editar") resetFormulario();
}

function wireDirty() {
  const campos = [el.txtTipo, el.txtEstado, el.txtDesde, el.txtHasta, el.txtNotas];
  campos.forEach(c => {
    c.addEventListener("input", () => {
      leerFormulario();
      setDirty(true);
    });
    c.addEventListener("change", () => {
      leerFormulario();
      setDirty(true);
    });
  });
}

function wireEventos() {
  el.btnNuevo.addEventListener("click", resetFormulario);

  el.btnAplicar.addEventListener("click", aplicarFiltros);
  el.btnLimpiar.addEventListener("click", limpiarFiltros);

  el.btnExportarCsv.addEventListener("click", () => {
    // Demo: placeholder
    alert("Demo: exportar CSV (en backend se generará archivo o stream).");
  });

  el.btnExportarPdf.addEventListener("click", () => {
    alert("Demo: exportar PDF (en backend se generará archivo o stream).");
  });

  el.tbody.addEventListener("click", (e) => {
    const btn = e.target.closest("button[data-accion]");
    if (!btn) return;

    const accion = btn.getAttribute("data-accion");
    const index = Number(btn.getAttribute("data-index"));

    if (accion === "editar") onEditar(index);
    if (accion === "eliminar") onEliminar(index);
  });

  el.btnCancelar.addEventListener("click", () => {
    if (estado.modo === "editar") {
      // vuelve a “no dirty” sin perder modo (puedes ajustar)
      setDirty(false);
      return;
    }
    resetFormulario();
  });

  el.btnGuardar.addEventListener("click", async () => {
    // Demo: simula guardado
    leerFormulario();

    if (!estado.formulario.desde || !estado.formulario.hasta) {
      alert("Completa el rango de fechas.");
      return;
    }

    if (estado.modo === "nuevo") {
      const nuevo = {
        folio: `RPT-${String(Math.floor(Math.random() * 90000) + 10000)}`,
        tipo: estado.formulario.tipo,
        rango: `${estado.formulario.desde} a ${estado.formulario.hasta}`,
        total: estado.formulario.tipo === "ventas" ? 1000 : 0,
        estado: estado.formulario.estado
      };
      estado.listado.unshift(nuevo);
    } else {
      // Actualiza el folio actual en el listado (demo)
      const folio = estado.formulario.folio;
      const idx = estado.listado.findIndex(x => x.folio === folio);
      if (idx >= 0) {
        estado.listado[idx] = {
          ...estado.listado[idx],
          tipo: estado.formulario.tipo,
          rango: `${estado.formulario.desde} a ${estado.formulario.hasta}`,
          estado: estado.formulario.estado
        };
      }
    }

    renderListado();
    setDirty(false);
    alert("Demo: cambios guardados.");
  });
}

(function init(){
  setFechasDefault();
  cargarDemo();
  resetFormulario();
  wireDirty();
  wireEventos();
})();
