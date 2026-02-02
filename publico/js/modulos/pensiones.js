document.addEventListener('DOMContentLoaded', () => {
    const ENDPOINT = `${URL_BASE}app/controladores/PensionesControlador.php`;

    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(document.getElementById('notificacion_toast'));
    const barra = document.getElementById('barra_guardado');
    const form = document.getElementById('form_pensiones');

    const lista = document.getElementById('lista_pensiones');
    const tabla = document.getElementById('tabla_pensiones');

    const contador = document.getElementById('contador_pensiones');
    const buscador = document.getElementById('buscador');

    const kpiActivas = document.getElementById('kpi_activas');
    const kpiSuspendidas = document.getElementById('kpi_suspendidas');
    const kpiIngresos = document.getElementById('kpi_ingresos');

    const btnNueva = document.getElementById('btn_nueva_pension');
    const btnIrFormulario = document.getElementById('btn_ir_formulario');

    const btnGuardar = document.getElementById('btn_guardar');
    const btnDescartar = document.getElementById('btn_descartar');

    const btnLimpiar = document.getElementById('btn_limpiar_formulario');
    const btnAbrirModalPago = document.getElementById('btn_abrir_modal_pago');

    const btnRefrescar = document.getElementById('btn_refrescar');
    const btnExportarFake = document.getElementById('btn_exportar_fake');

    // Historial de pagos
    const tablaPagos = document.getElementById('tabla_pagos');
    // Tarjeta vigencia
    const tarjetaVigencia = document.getElementById('tarjeta_vigencia');
    const vigenciaDias = document.getElementById('vigencia_dias');
    const vigenciaBadge = document.getElementById('vigencia_badge');
    const vigenciaDetalle = document.getElementById('vigencia_detalle');

    const textoHistorial = document.getElementById('texto_historial');
    const badgeTotalPagos = document.getElementById('badge_total_pagos');
    const badgeTotalMXN = document.getElementById('badge_total_mxn');

    // Modal pago
    const modalPagoEl = document.getElementById('modal_pago');
    const modalPago = modalPagoEl ? new bootstrap.Modal(modalPagoEl) : null;

    const pagoMonto = document.getElementById('pago_monto');
    const pagoMetodo = document.getElementById('pago_metodo');
    const pagoReferencia = document.getElementById('pago_referencia');
    const pagoDias = document.getElementById('pago_dias_extension');
    const pagoNotas = document.getElementById('pago_notas');
    const btnConfirmarPago = document.getElementById('btn_confirmar_pago');

    const f = {
        pension_id: document.getElementById('pension_id'),
        cliente_nombre: document.getElementById('cliente_nombre'),
        cliente_telefono: document.getElementById('cliente_telefono'),
        vehiculo_placa: document.getElementById('vehiculo_placa'),
        vehiculo_tipo: document.getElementById('vehiculo_tipo'),
        plan_tipo: document.getElementById('plan_tipo'),
        monto_mxn: document.getElementById('monto_mxn'),
        vigencia_inicio: document.getElementById('vigencia_inicio'),
        vigencia_fin: document.getElementById('vigencia_fin'),
        notas: document.getElementById('notas'),
        estatus_activa: document.getElementById('estatus_activa'),
    };

    let pensiones = [];
    let pensionSeleccionadaId = null;
    let pagosActuales = [];

    /* =========================
       Helpers UI
       ========================= */
    const hoyISO = () => new Date().toISOString().slice(0, 10);

    const sumarDias = (iso, dias) => {
        const d = new Date(iso + "T00:00:00");
        d.setDate(d.getDate() + dias);
        return d.toISOString().slice(0, 10);
    };

    const formatoMXN = (n) => {
        const num = Number(n || 0);
        return num.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    };

    const iniciales = (nombre) => {
        const parts = (nombre || "").trim().split(/\s+/).slice(0, 2);
        return parts.map(p => p[0]?.toUpperCase() || "").join("");
    };

    const rango = (ini, fin) => `${ini || "—"} → ${fin || "—"}`;

    const mostrarToast = (mensaje, esError = false) => {
        const body = document.getElementById('mensaje_toast');
        body.textContent = mensaje;

        const headerIcon = document.querySelector('#notificacion_toast .toast-header i');
        if (headerIcon) {
            headerIcon.className = esError
                ? "bi bi-exclamation-triangle text-warning me-2"
                : "bi bi-check2-circle text-success me-2";
        }
        toastBootstrap.show();
    };

    const formatearFechaHora = (fechaSQL) => {
        // fechaSQL: "YYYY-MM-DD HH:MM:SS"
        if (!fechaSQL) return "—";
        const s = fechaSQL.replace(" ", "T");
        const d = new Date(s);
        if (isNaN(d.getTime())) return fechaSQL;
        return d.toLocaleString('es-MX', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    };

    const diasEntre = (fechaISOa, fechaISOb) => {
        // diferencia en días (b - a), redondeo hacia arriba
        const a = new Date(fechaISOa + "T00:00:00");
        const b = new Date(fechaISOb + "T00:00:00");
        const ms = b.getTime() - a.getTime();
        return Math.ceil(ms / (1000 * 60 * 60 * 24));
    };

    const pintarTarjetaVigencia = (pension) => {
        if (!tarjetaVigencia) return;

        if (!pension || !pension.vigencia_inicio || !pension.vigencia_fin) {
            tarjetaVigencia.style.display = "none";
            return;
        }

        tarjetaVigencia.style.display = "";
        const hoy = hoyISO();
        const fin = pension.vigencia_fin;

        const diasRestantes = diasEntre(hoy, fin);

        // Detalle
        vigenciaDetalle.textContent = `Vigencia: ${pension.vigencia_inicio} → ${pension.vigencia_fin}`;

        // KPI
        vigenciaDias.textContent = String(Math.max(diasRestantes, 0));

        // Badge: vencida / por vencer / al corriente
        // Umbral "por vencer" = 3 días (puedes ajustarlo)
        const umbralPorVencer = 3;

        if (diasRestantes <= 0) {
            vigenciaBadge.className = "badge-estado bad";
            vigenciaBadge.innerHTML = `<i class="bi bi-x-circle"></i>Vencida`;
        } else if (diasRestantes <= umbralPorVencer) {
            vigenciaBadge.className = "badge-estado warn";
            vigenciaBadge.innerHTML = `<i class="bi bi-exclamation-triangle"></i>Por vencer`;
        } else {
            vigenciaBadge.className = "badge-estado ok";
            vigenciaBadge.innerHTML = `<i class="bi bi-check-circle"></i>Al corriente`;
        }
    };


    /* =========================
       Dirty checking
       ========================= */
    let firmaInicial = "";

    const serializarFormulario = () => {
        const fd = new FormData(form);
        const obj = {};
        for (const [k, v] of fd.entries()) obj[k] = v;
        obj.estatus_activa = f.estatus_activa.checked ? "1" : "0";
        return JSON.stringify(obj);
    };

    const marcarDirty = () => {
        if (!barra) return;
        const actual = serializarFormulario();
        if (actual !== firmaInicial) barra.classList.add('visible');
        else barra.classList.remove('visible');
    };

    const fijarBaseline = () => {
        firmaInicial = serializarFormulario();
        barra.classList.remove('visible');
    };

    /* =========================
       API
       ========================= */
    const apiGet = async (accion, params = {}) => {
        const url = new URL(`${ENDPOINT}`);
        url.searchParams.set('accion', accion);
        Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

        const res = await fetch(url.toString());
        return await res.json();
    };

    const apiPost = async (accion, formData) => {
        const res = await fetch(`${ENDPOINT}?accion=${encodeURIComponent(accion)}`, {
            method: 'POST',
            body: formData
        });
        return await res.json();
    };

    /* =========================
       Render historial pagos
       ========================= */
    const renderHistorialPagos = (pagos = []) => {
        pagosActuales = Array.isArray(pagos) ? pagos : [];

        const total = pagosActuales.length;
        const totalMXN = pagosActuales.reduce((acc, p) => acc + Number(p.monto_mxn || 0), 0);

        badgeTotalPagos.innerHTML = `<i class="bi bi-list-ul me-1"></i>${total} pagos`;
        badgeTotalMXN.innerHTML = `<i class="bi bi-cash-stack me-1"></i>${formatoMXN(totalMXN)}`;

        if (!pensionSeleccionadaId) {
            textoHistorial.textContent = "Guarda/selecciona una pensión para ver su historial.";
        } else {
            textoHistorial.textContent = "Pagos registrados para la pensión seleccionada.";
        }

        if (total === 0) {
            tablaPagos.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">
                        <i class="bi bi-receipt me-2"></i>Sin pagos para mostrar.
                    </td>
                </tr>
            `;
            return;
        }

        tablaPagos.innerHTML = pagosActuales.map(p => `
            <tr>
                <td>${formatearFechaHora(p.fecha_pago)}</td>
                <td class="fw-bold">${formatoMXN(p.monto_mxn)}</td>
                <td>${p.metodo_pago || "—"}</td>
                <td class="text-secondary">${p.referencia || "—"}</td>
                <td class="text-end text-secondary">${(p.notas || "").slice(0, 28)}${(p.notas || "").length > 28 ? "…" : ""}</td>
            </tr>
        `).join("");
    };

    /* =========================
       Render listados
       ========================= */
    const calcularKpis = () => {
        const activas = pensiones.filter(p => Number(p.esta_activa) === 1).length;
        const suspendidas = pensiones.length - activas;
        const ingresos = pensiones
            .filter(p => Number(p.esta_activa) === 1)
            .reduce((acc, p) => acc + Number(p.monto_mxn || 0), 0);

        kpiActivas.textContent = String(activas);
        kpiSuspendidas.textContent = String(suspendidas);
        kpiIngresos.textContent = formatoMXN(ingresos);
    };

    const aplicarFiltro = (listaBase, filtro = "") => {
        const q = (filtro || "").toLowerCase().trim();
        if (!q) return listaBase;

        return listaBase.filter(p => {
            const hay = [
                p.cliente_nombre,
                p.vehiculo_placa,
                p.plan_nombre,
                String(p.id)
            ].join(" ").toLowerCase();
            return hay.includes(q);
        });
    };

    const renderLista = (filtro = "") => {
        const datos = aplicarFiltro(pensiones, filtro);
        contador.textContent = String(datos.length);

        lista.innerHTML = datos.map(p => {
            const activo = p.id === pensionSeleccionadaId ? "activo" : "";
            const estado = Number(p.esta_activa) === 1
                ? `<span class="badge badge-soft"><i class="bi bi-check-circle me-1"></i>Activa</span>`
                : `<span class="badge text-bg-secondary"><i class="bi bi-pause-circle me-1"></i>Suspendida</span>`;

            return `
                <div class="item-mini ${activo}" data-id="${p.id}">
                    <div class="avatar-mini">${iniciales(p.cliente_nombre)}</div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${p.cliente_nombre}</div>
                        <div class="sub text-uppercase">${p.vehiculo_placa} • ${p.plan_nombre || "—"} • ${formatoMXN(p.monto_mxn)}</div>
                    </div>
                    <div class="text-end">${estado}</div>
                </div>
            `;
        }).join("");

        lista.querySelectorAll('.item-mini').forEach(el => {
            el.addEventListener('click', () => {
                const id = Number(el.dataset.id);
                cargarEnFormularioDesdeApi(id);
                btnIrFormulario.click();
            });
        });
    };

    const renderTabla = (filtro = "") => {
        const datos = aplicarFiltro(pensiones, filtro);

        tabla.innerHTML = datos.map(p => {
            const estado = Number(p.esta_activa) === 1
                ? `<span class="badge badge-soft"><i class="bi bi-check-circle me-1"></i>Activa</span>`
                : `<span class="badge text-bg-secondary"><i class="bi bi-pause-circle me-1"></i>Suspendida</span>`;

            return `
                <tr>
                    <td>
                        <div class="fw-bold">${p.cliente_nombre}</div>
                        <div class="text-secondary small"><i class="bi bi-telephone me-1"></i>${p.cliente_telefono || "—"}</div>
                    </td>
                    <td class="text-uppercase">${p.vehiculo_placa || "—"}</td>
                    <td>
                        <div class="fw-bold">${p.plan_nombre || "—"}</div>
                        <div class="text-secondary small">${formatoMXN(p.monto_mxn)}</div>
                    </td>
                    <td class="text-secondary small">${rango(p.vigencia_inicio, p.vigencia_fin)}</td>
                    <td>${estado}</td>
                    <td class="text-end">
                        <button class="btn btn-lg btn-soft btn-editar" data-id="${p.id}">
                            <i class="bi bi-pencil me-2"></i>Editar
                        </button>
                    </td>
                </tr>
            `;
        }).join("");

        tabla.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                cargarEnFormularioDesdeApi(id);
                btnIrFormulario.click();
            });
        });
    };

    /* =========================
       Formulario
       ========================= */
    const limpiarFormulario = () => {
        pensionSeleccionadaId = null;
        f.pension_id.value = "";
        f.cliente_nombre.value = "";
        f.cliente_telefono.value = "";
        f.vehiculo_placa.value = "";
        f.vehiculo_tipo.value = "Automóvil";
        f.plan_tipo.value = "";
        f.monto_mxn.value = "0";
        f.vigencia_inicio.value = hoyISO();
        f.vigencia_fin.value = sumarDias(hoyISO(), 30);
        f.notas.value = "";
        f.estatus_activa.checked = true;

        btnAbrirModalPago.disabled = true;
        renderHistorialPagos([]);
        pintarTarjetaVigencia(null);


        renderLista(buscador.value);
        renderTabla(buscador.value);
        fijarBaseline();
    };

    const cargarEnFormulario = (p) => {
        if (!p) return;

        pensionSeleccionadaId = Number(p.id);

        f.pension_id.value = String(p.id);
        f.cliente_nombre.value = p.cliente_nombre || "";
        f.cliente_telefono.value = p.cliente_telefono || "";
        f.vehiculo_placa.value = (p.vehiculo_placa || "").toUpperCase();
        f.vehiculo_tipo.value = p.vehiculo_tipo || "Automóvil";
        f.plan_tipo.value = p.plan_nombre || "";
        f.monto_mxn.value = String(p.monto_mxn ?? 0);
        f.vigencia_inicio.value = p.vigencia_inicio || hoyISO();
        f.vigencia_fin.value = p.vigencia_fin || sumarDias(hoyISO(), 30);
        f.notas.value = p.notas || "";
        f.estatus_activa.checked = Number(p.esta_activa) === 1;

        btnAbrirModalPago.disabled = false;

        renderLista(buscador.value);
        renderTabla(buscador.value);
        pintarTarjetaVigencia(p);

        fijarBaseline();
    };

    const cargarEnFormularioDesdeApi = async (id) => {
        const json = await apiGet('obtener', { id });
        if (!json.exito) {
            mostrarToast(json.mensaje || 'No se pudo obtener la pensión', true);
            return;
        }

        cargarEnFormulario(json.datos.pension);
        renderHistorialPagos(json.datos.pagos || []);
    };

    /* =========================
       Guardar pensión
       ========================= */
    const guardar = async () => {
        const fd = new FormData(form);

        const json = await apiPost('guardar', fd);
        if (!json.exito) {
            mostrarToast(json.mensaje || 'Error al guardar', true);
            return;
        }

        mostrarToast(json.mensaje || 'Guardado');
        await cargarListado();

        const id = json.datos?.id ? Number(json.datos.id) : 0;
        if (id > 0) await cargarEnFormularioDesdeApi(id);
    };

    /* =========================
       Modal pago
       ========================= */
    const abrirModalPago = () => {
        const id = Number(f.pension_id.value || 0);
        if (id <= 0) {
            mostrarToast("Primero guarda la pensión para registrar pagos.", true);
            return;
        }

        // Defaults pro: monto = monto de la pensión
        pagoMonto.value = String(Number(f.monto_mxn.value || 0) || "");
        pagoMetodo.value = "Efectivo";
        pagoReferencia.value = "";
        pagoDias.value = ""; // opcional, backend infiere
        pagoNotas.value = "";

        modalPago?.show();
        // No ensuciamos el formulario principal
    };

    const confirmarPago = async () => {
        const id = Number(f.pension_id.value || 0);
        if (id <= 0) {
            mostrarToast("ID inválido de pensión.", true);
            return;
        }

        const monto = Number(pagoMonto.value || 0);
        if (monto <= 0) {
            mostrarToast("Ingresa un monto válido.", true);
            return;
        }

        const fd = new FormData();
        fd.append('pension_id', String(id));
        fd.append('monto_mxn', String(monto));
        fd.append('metodo_pago', (pagoMetodo.value || 'Efectivo'));
        fd.append('referencia', (pagoReferencia.value || '').trim());
        fd.append('notas', (pagoNotas.value || '').trim());
        fd.append('usuario', 'sistema');

        const dias = Number(pagoDias.value || 0);
        if (dias > 0) fd.append('dias_extension', String(dias));

        btnConfirmarPago.disabled = true;
        btnConfirmarPago.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Renovando...`;

        const json = await apiPost('registrar_pago', fd);

        btnConfirmarPago.disabled = false;
        btnConfirmarPago.innerHTML = `<i class="bi bi-arrow-repeat me-2"></i>Renovar ahora`;


        if (!json.exito) {
            mostrarToast(json.mensaje || 'No se pudo registrar el pago', true);
            return;
        }

        // Backend devuelve pension actualizada + pagos
        const pension = json.datos?.pension;
        const pagos = json.datos?.pagos || [];

        // Refresca formulario y historial sin “ensuciar” (esto es resultado de acción)
        if (pension) cargarEnFormulario(pension);
        renderHistorialPagos(pagos);

        await cargarListado();

        modalPago?.hide();
        mostrarToast(json.mensaje || 'Pago registrado');
    };

    /* =========================
       Cargar listado
       ========================= */
    const cargarListado = async () => {
        const json = await apiGet('listar', { busqueda: buscador.value || '' });
        if (!json.exito) {
            mostrarToast(json.mensaje || 'No se pudo cargar el listado', true);
            return;
        }
        pensiones = json.datos.pensiones || [];
        renderLista(buscador.value);
        renderTabla(buscador.value);
        calcularKpis();
    };

    /* =========================
       Eventos
       ========================= */
    form.addEventListener('input', marcarDirty);
    form.addEventListener('change', marcarDirty);

    btnGuardar.addEventListener('click', guardar);

    btnDescartar.addEventListener('click', async () => {
        if (pensionSeleccionadaId) await cargarEnFormularioDesdeApi(pensionSeleccionadaId);
        else limpiarFormulario();
        mostrarToast("Cambios descartados.");
    });

    btnNueva.addEventListener('click', () => {
        limpiarFormulario();
        btnIrFormulario.click();
    });

    btnLimpiar?.addEventListener('click', () => {
        limpiarFormulario();
        mostrarToast("Formulario limpio.");
    });

    btnAbrirModalPago?.addEventListener('click', abrirModalPago);
    btnConfirmarPago?.addEventListener('click', confirmarPago);

    btnRefrescar?.addEventListener('click', async () => {
        await cargarListado();
        mostrarToast("Listado refrescado.");
    });

    btnExportarFake?.addEventListener('click', () => {
        mostrarToast("Exportación pendiente (UI lista).");
    });

    buscador.addEventListener('input', async () => {
        await cargarListado();
    });

    // Limpieza modal al cerrarse (solo visual)
    modalPagoEl?.addEventListener('hidden.bs.modal', () => {
        // no hace falta, pero deja UI lista para siguiente uso
        pagoMonto.value = "";
        pagoReferencia.value = "";
        pagoDias.value = "";
        pagoNotas.value = "";
        pagoMetodo.value = "Efectivo";
    });

    /* =========================
       Init
       ========================= */
    f.vigencia_inicio.value = hoyISO();
    f.vigencia_fin.value = sumarDias(hoyISO(), 30);

    limpiarFormulario();
    cargarListado();
});
