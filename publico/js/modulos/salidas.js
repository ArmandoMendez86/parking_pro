document.addEventListener('DOMContentLoaded', () => {
    iniciarReloj();
    configurarEventos();
});

const ui = {
    inputBusqueda: document.getElementById('input_busqueda'),
    btnBuscar: document.getElementById('btn_buscar'),
    panelCobro: document.getElementById('panel_cobro'),
    panelVacio: document.getElementById('panel_vacio'),
    barraAcciones: document.getElementById('barra_acciones'),

    txtTicket: document.getElementById('txt_ticket_id'),
    txtPlaca: document.getElementById('txt_placa'),
    txtTarifa: document.getElementById('txt_tarifa_nombre'),
    txtEntrada: document.getElementById('txt_entrada'),
    txtFechaEntrada: document.getElementById('txt_fecha_entrada'),
    txtSalida: document.getElementById('txt_salida'),
    txtTiempo: document.getElementById('txt_tiempo_total'),
    txtTotal: document.getElementById('txt_total'),

    inputRecibido: document.getElementById('input_recibido'),
    txtCambio: document.getElementById('txt_cambio'),

    btnCancelar: document.getElementById('btn_cancelar'),
    btnConfirmar: document.getElementById('btn_confirmar_salida'),

    btnToggleDetalles: document.getElementById('btn_toggle_detalles'),
    bloqueDetalles: document.getElementById('bloque_detalles_cobro'),
    collapseDetalles: document.getElementById('collapse_detalles_cobro'),

    // BOLETO PERDIDO
    bloqueBoletoPerdido: document.getElementById('bloque_boleto_perdido'),
    chkBoletoPerdido: document.getElementById('chk_boleto_perdido'),

    // DESCUENTO
    bloqueDescuento: document.getElementById('bloque_descuento'),
    selDescuentoTipo: document.getElementById('sel_descuento_tipo'),
    inputDescuentoValor: document.getElementById('input_descuento_valor'),
    inputDescuentoMotivo: document.getElementById('input_descuento_motivo'),
    txtDescuentoHint: document.getElementById('txt_descuento_hint'),
    badgeDescuentoEstado: document.getElementById('badge_descuento_estado'),
    txtDescuentoResumen: document.getElementById('txt_descuento_resumen'),
    txtDescuentoMonto: document.getElementById('txt_descuento_monto')
};

const RUTA_CONTROLADOR = `${window.URL_BASE || ''}app/controladores/SalidasControlador.php`;

let modoPantalla = null; // 'PENSION' | 'INGRESO'
let ingresoActual = null;
let pensionActual = null;

let totalBase = 0;             // total por tiempo (sin extras)
let extraBoletoPerdido = 0;    // recargo configurado por tarifa
let totalPagar = 0;            // total final (subtotal - descuento)
let subtotalActual = 0;        // subtotal = totalBase + recargo (si aplica)

let descuentoTipo = '';
let descuentoValor = 0;
let descuentoMonto = 0;
let descuentoMotivo = '';

let calculoActual = null; // guarda el cálculo real (minutos_totales, fecha_salida, etc.)

function configurarEventos() {
    ui.inputBusqueda?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') buscarTermino();
    });

    ui.btnBuscar?.addEventListener('click', buscarTermino);

    ui.inputRecibido?.addEventListener('input', () => {
        calcularCambio();
        sincronizarBarraAccion();
    });

    ui.chkBoletoPerdido?.addEventListener('change', () => {
        actualizarBadgeBoletoPerdido();
        recalcularTotalesYUI();

        if (ingresoActual && calculoActual) renderizarDetallesCobroIngreso(ingresoActual, calculoActual);

        calcularCambio();
        sincronizarBarraAccion();
    });

    ui.selDescuentoTipo?.addEventListener('change', () => {
        onCambioTipoDescuento();
        recalcularTotalesYUI();

        if (ingresoActual && calculoActual) renderizarDetallesCobroIngreso(ingresoActual, calculoActual);

        calcularCambio();
        sincronizarBarraAccion();
    });

    ui.inputDescuentoValor?.addEventListener('input', () => {
        sincronizarDescuentoDesdeUI();
        recalcularTotalesYUI();

        if (ingresoActual && calculoActual) renderizarDetallesCobroIngreso(ingresoActual, calculoActual);

        calcularCambio();
        sincronizarBarraAccion();
    });

    ui.inputDescuentoMotivo?.addEventListener('input', () => {
        descuentoMotivo = (ui.inputDescuentoMotivo.value || '').trim().slice(0, 255);
        sincronizarBarraAccion();
    });

    ui.btnCancelar?.addEventListener('click', () => reiniciarInterfaz(true));
    ui.btnConfirmar?.addEventListener('click', registrarSalidaIngreso);
}

async function buscarTermino() {
    const termino = (ui.inputBusqueda.value || '').trim().toUpperCase();
    if (!termino) {
        mostrarError("Por favor ingrese una placa o ticket.");
        return;
    }

    bloquearBotonBuscar(true);

    try {
        const resp = await fetch(`${RUTA_CONTROLADOR}?accion=buscar_placa`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'buscar_placa', termino })
        });

        const data = await resp.json();

        if (!resp.ok || !data?.exito) {
            throw new Error(data?.mensaje || 'No se encontró información.');
        }

        const tipo = data?.datos?.tipo_resultado;
        const calculo = data?.datos?.calculo;

        if (!tipo || !calculo) {
            throw new Error('Respuesta incompleta del servidor.');
        }

        if (tipo === 'PENSION') {
            cargarPantallaPension(data.datos.pension, calculo);
        } else {
            cargarPantallaIngreso(data.datos.ingreso, calculo);
        }

    } catch (e) {
        await Swal.fire({
            icon: 'error',
            title: 'Atención',
            text: e.message || 'Error al buscar.',
            confirmButtonColor: '#4f46e5'
        });
        reiniciarInterfaz(false);
    } finally {
        bloquearBotonBuscar(false);
    }
}

function cargarPantallaPension(pension) {
    modoPantalla = 'PENSION';
    pensionActual = pension || null;
    ingresoActual = null;
    calculoActual = null;

    totalBase = 0;
    extraBoletoPerdido = 0;
    subtotalActual = 0;
    totalPagar = 0;

    resetDescuento();

    ui.panelVacio.style.display = 'none';
    ui.panelCobro.style.display = 'block';

    ui.txtTicket.textContent = pension?.id ?? '--';
    ui.txtPlaca.textContent = pension?.vehiculo_placa ?? '---';
    ui.txtTarifa.innerHTML = `<i class="bi bi-shield-check me-2"></i>EN PENSIÓN`;

    ui.txtEntrada.textContent = pension?.vigencia_inicio ? formatearFechaSolo(pension.vigencia_inicio) : '--/--/----';
    ui.txtFechaEntrada.textContent = pension?.plan_nombre ? pension.plan_nombre : 'Plan';
    ui.txtSalida.textContent = pension?.vigencia_fin ? formatearFechaSolo(pension.vigencia_fin) : '--/--/----';

    ui.txtTiempo.textContent = 'Pensión activa';
    ui.txtTotal.textContent = `$0.00`;

    ocultarDetallesCobro();
    ocultarBoletoPerdido();
    ocultarDescuento();

    ui.inputRecibido.disabled = true;
    ui.inputRecibido.value = '';
    ui.inputRecibido.placeholder = "No aplica en pensiones";

    ui.txtCambio.textContent = '$0.00';
    ui.txtCambio.classList.remove('text-danger');
    ui.txtCambio.classList.add('text-success');

    ui.btnConfirmar.disabled = true;
    ui.barraAcciones.classList.remove('visible');

    Swal.fire({
        icon: 'info',
        title: 'Vehículo en Pensión',
        text: `Cliente: ${pension?.cliente_nombre || 'N/D'} | Vigencia: ${pension?.vigencia_inicio || ''} a ${pension?.vigencia_fin || ''}`,
        confirmButtonColor: '#4f46e5'
    });
}

function cargarPantallaIngreso(ingreso, calculo) {
    modoPantalla = 'INGRESO';
    ingresoActual = ingreso || null;
    pensionActual = null;
    calculoActual = calculo || null;

    totalBase = parseFloat(calculo?.monto_total) || 0;
    extraBoletoPerdido = parseFloat(ingreso?.costo_boleto_perdido) || 0;

    const elMontoBoleto = document.getElementById('txt_monto_boleto');
    if (elMontoBoleto) elMontoBoleto.textContent = extraBoletoPerdido.toFixed(2);

    if (ui.chkBoletoPerdido) ui.chkBoletoPerdido.checked = false;
    actualizarBadgeBoletoPerdido();

    resetDescuento();
    mostrarDescuentoSiAplica();

    recalcularTotalesYUI();

    ui.panelVacio.style.display = 'none';
    ui.panelCobro.style.display = 'block';

    ui.txtTicket.textContent = ingreso?.id ?? '--';
    ui.txtPlaca.textContent = ingreso?.placa ?? '---';
    ui.txtTarifa.textContent = ingreso?.tipo_vehiculo ?? '--';

    const fechaIngreso = ingreso?.fecha_ingreso;
    const fechaSalida = calculo?.fecha_salida;

    ui.txtEntrada.textContent = formatearHoraDesdeMySQL(fechaIngreso);
    ui.txtFechaEntrada.textContent = formatearFechaCortaDesdeMySQL(fechaIngreso);
    ui.txtSalida.textContent = formatearHoraDesdeMySQL(fechaSalida);

    const minutosTotales = parseInt(calculo?.minutos_totales, 10) || 0;
    const horas = Math.floor(minutosTotales / 60);
    const minutosRestantes = minutosTotales % 60;
    ui.txtTiempo.textContent = `${horas} h ${minutosRestantes} min`;

    // Mostrar detalles (compacto) pero colapsado por default
    mostrarDetallesCobro();
    renderizarDetallesCobroIngreso(ingreso, calculo);
    colapsarDetalles(true);

    // Mostrar/ocultar boleto perdido
    mostrarBoletoPerdidoSiAplica();

    ui.inputRecibido.disabled = false;
    ui.inputRecibido.value = '';
    ui.inputRecibido.placeholder = "0.00";
    ui.inputRecibido.focus();

    ui.txtCambio.textContent = '$0.00';
    ui.txtCambio.classList.remove('text-danger');
    ui.txtCambio.classList.add('text-success');

    validarBotonConfirmacion();
    sincronizarBarraAccion();
}

function actualizarBadgeBoletoPerdido() {
    const badge = document.getElementById('badge_boleto_perdido');
    if (!badge) return;

    if (!!ui.chkBoletoPerdido?.checked) {
        badge.classList.remove('bg-warning', 'text-dark');
        badge.classList.add('bg-danger', 'text-white');
        badge.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Recargo activo`;
    } else {
        badge.classList.remove('bg-danger', 'text-white');
        badge.classList.add('bg-warning', 'text-dark');
        badge.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Aplica recargo`;
    }
}

function mostrarBoletoPerdidoSiAplica() {
    if (!ui.bloqueBoletoPerdido || !ui.chkBoletoPerdido) return;
    const aplica = (modoPantalla === 'INGRESO' && ingresoActual && extraBoletoPerdido > 0);
    ui.bloqueBoletoPerdido.style.display = aplica ? '' : 'none';
}

function ocultarBoletoPerdido() {
    if (ui.bloqueBoletoPerdido) ui.bloqueBoletoPerdido.style.display = 'none';
    if (ui.chkBoletoPerdido) ui.chkBoletoPerdido.checked = false;
}

function mostrarDescuentoSiAplica() {
    if (!ui.bloqueDescuento) return;
    const aplica = (modoPantalla === 'INGRESO' && ingresoActual);
    ui.bloqueDescuento.style.display = aplica ? '' : 'none';
}

function ocultarDescuento() {
    if (ui.bloqueDescuento) ui.bloqueDescuento.style.display = 'none';
    resetDescuento();
}

function onCambioTipoDescuento() {
    descuentoTipo = (ui.selDescuentoTipo?.value || '').trim();

    const habilitar = !!descuentoTipo;
    if (ui.inputDescuentoValor) ui.inputDescuentoValor.disabled = !habilitar;
    if (ui.inputDescuentoMotivo) ui.inputDescuentoMotivo.disabled = !habilitar;

    if (!habilitar) {
        resetDescuento();
        pintarEstadoDescuento();
        return;
    }

    if (ui.txtDescuentoHint) {
        if (descuentoTipo === 'PORCENTAJE') ui.txtDescuentoHint.textContent = 'Ej. 10 = 10% (máx 100).';
        else if (descuentoTipo === 'MONTO') ui.txtDescuentoHint.textContent = 'Monto en $ a descontar (se limita al subtotal).';
        else ui.txtDescuentoHint.textContent = 'Horas a descontar (se multiplica por costo/h).';
    }

    if (ui.inputDescuentoValor && (ui.inputDescuentoValor.value || '').trim() === '') {
        ui.inputDescuentoValor.value = '0';
    }
    sincronizarDescuentoDesdeUI();
    pintarEstadoDescuento();
}

function resetDescuento() {
    descuentoTipo = '';
    descuentoValor = 0;
    descuentoMonto = 0;
    descuentoMotivo = '';

    if (ui.selDescuentoTipo) ui.selDescuentoTipo.value = '';
    if (ui.inputDescuentoValor) {
        ui.inputDescuentoValor.value = '';
        ui.inputDescuentoValor.disabled = true;
    }
    if (ui.inputDescuentoMotivo) {
        ui.inputDescuentoMotivo.value = '';
        ui.inputDescuentoMotivo.disabled = true;
    }
    if (ui.txtDescuentoHint) ui.txtDescuentoHint.textContent = 'Seleccione un tipo para capturar el valor.';
    pintarEstadoDescuento();
    pintarResumenDescuento();
}

function sincronizarDescuentoDesdeUI() {
    descuentoTipo = (ui.selDescuentoTipo?.value || '').trim();
    descuentoValor = parseFloat(ui.inputDescuentoValor?.value) || 0;
    if (descuentoValor < 0) descuentoValor = 0;
    descuentoMotivo = (ui.inputDescuentoMotivo?.value || '').trim().slice(0, 255);

    if (descuentoTipo === 'PORCENTAJE') {
        if (descuentoValor > 100) descuentoValor = 100;
        if (ui.inputDescuentoValor) ui.inputDescuentoValor.value = String(descuentoValor);
    }
    pintarEstadoDescuento();
}

function pintarEstadoDescuento() {
    if (!ui.badgeDescuentoEstado) return;

    if (!descuentoTipo) {
        ui.badgeDescuentoEstado.className = 'badge rounded-pill bg-white text-dark border';
        ui.badgeDescuentoEstado.innerHTML = `<i class="bi bi-slash-circle me-1"></i>Sin descuento`;
        return;
    }

    const icono = (descuentoTipo === 'PORCENTAJE') ? 'bi-percent' : (descuentoTipo === 'MONTO' ? 'bi-cash-coin' : 'bi-clock');
    ui.badgeDescuentoEstado.className = 'badge rounded-pill bg-white text-dark border';
    ui.badgeDescuentoEstado.innerHTML = `<i class="bi ${icono} me-1"></i>${descuentoTipo}`;
}

function pintarResumenDescuento() {
    if (!ui.txtDescuentoResumen || !ui.txtDescuentoMonto) return;

    const mostrar = (descuentoMonto > 0.00001);
    ui.txtDescuentoResumen.style.display = mostrar ? '' : 'none';
    ui.txtDescuentoMonto.textContent = `$${descuentoMonto.toFixed(2)}`;
}

function recalcularTotalesYUI() {
    if (modoPantalla !== 'INGRESO' || !ingresoActual) return;

    const marcado = !!ui.chkBoletoPerdido?.checked;
    subtotalActual = (marcado ? (totalBase + extraBoletoPerdido) : totalBase);

    descuentoMonto = calcularDescuentoMonto(descuentoTipo, descuentoValor, subtotalActual, ingresoActual);

    totalPagar = round2(Math.max(0, subtotalActual - descuentoMonto));

    ui.txtTotal.textContent = `$${totalPagar.toFixed(2)}`;
    pintarResumenDescuento();

    validarBotonConfirmacion();
}

function calcularDescuentoMonto(tipo, valor, subtotal, ingreso) {
    tipo = (tipo || '').trim();
    valor = parseFloat(valor) || 0;
    subtotal = parseFloat(subtotal) || 0;
    if (!tipo || valor <= 0 || subtotal <= 0) return 0;

    let m = 0;

    if (tipo === 'PORCENTAJE') {
        const pct = Math.min(100, Math.max(0, valor));
        m = subtotal * (pct / 100);
    } else if (tipo === 'MONTO') {
        m = valor;
    } else if (tipo === 'HORAS') {
        const costoHora = parseFloat(ingreso?.costo_hora) || 0;
        m = valor * costoHora;
    } else {
        m = 0;
    }

    if (!isFinite(m)) m = 0;
    m = Math.max(0, m);
    m = Math.min(subtotal, m);

    return round2(m);
}

function renderizarDetallesCobroIngreso(ingreso, calculo) {
    const tolerancia = parseInt(ingreso?.tolerancia_extra_minutos, 10) || 0;
    const costoHora = parseFloat(ingreso?.costo_hora) || 0;
    const costoFraccion = parseFloat(ingreso?.costo_fraccion_extra) || 0;

    const minutosTotales = parseInt(calculo?.minutos_totales, 10) || 0;
    const horasCompletas = Math.floor(minutosTotales / 60);
    const minutosRestantes = minutosTotales % 60;

    const regla = obtenerReglaAplicada(minutosTotales, tolerancia);

    let linea1 = '';
    let linea2 = '';
    let linea3 = '';

    if (minutosTotales <= tolerancia) {
        linea1 = `<div class="d-flex justify-content-between"><span class="text-muted">Dentro de tolerancia</span><span class="fw-semibold">$0.00</span></div>`;
    } else if (minutosTotales < 60) {
        linea1 = `<div class="d-flex justify-content-between"><span class="text-muted">1 hora mínima</span><span class="fw-semibold">$${costoHora.toFixed(2)}</span></div>`;
    } else {
        const montoHoras = horasCompletas * costoHora;
        linea1 = `<div class="d-flex justify-content-between"><span class="text-muted">${horasCompletas} h x $${costoHora.toFixed(2)}</span><span class="fw-semibold">$${montoHoras.toFixed(2)}</span></div>`;

        if (minutosRestantes > 0) {
            if (minutosRestantes <= tolerancia) {
                linea2 = `<div class="d-flex justify-content-between"><span class="text-muted">Restante ${minutosRestantes} min (tolerancia)</span><span class="fw-semibold">$0.00</span></div>`;
            } else {
                linea2 = `<div class="d-flex justify-content-between"><span class="text-muted">Fracción extra</span><span class="fw-semibold">$${costoFraccion.toFixed(2)}</span></div>`;
            }
        }
    }

    linea3 = `
        <div class="d-flex flex-wrap gap-2 mt-2">
            <span class="badge text-bg-light border"><i class="bi bi-shield-check me-1"></i>${tolerancia} min</span>
            <span class="badge text-bg-light border"><i class="bi bi-clock me-1"></i>$${costoHora.toFixed(2)}/h</span>
            <span class="badge text-bg-light border"><i class="bi bi-hourglass-split me-1"></i>$${costoFraccion.toFixed(2)} fracc.</span>
        </div>
    `;

    const marcadoBoleto = !!ui.chkBoletoPerdido?.checked;
    const lineaExtraBoleto = (marcadoBoleto && extraBoletoPerdido > 0)
        ? `<div class="d-flex justify-content-between mt-2"><span class="text-muted"><i class="bi bi-exclamation-triangle me-1 text-warning"></i>Boleto perdido</span><span class="fw-semibold">$${extraBoletoPerdido.toFixed(2)}</span></div>`
        : '';

    const lineaSubtotal = `<div class="d-flex justify-content-between mt-2"><span class="text-muted fw-bold">Subtotal</span><span class="fw-bold">$${subtotalActual.toFixed(2)}</span></div>`;

    const lineaDescuento = (descuentoMonto > 0)
        ? `<div class="d-flex justify-content-between mt-2"><span class="text-muted"><i class="bi bi-tag me-1 text-primary"></i>Descuento</span><span class="fw-semibold text-danger">- $${descuentoMonto.toFixed(2)}</span></div>`
        : '';

    const html = `
        <div class="p-3 bg-light rounded-4 border">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-bold"><i class="bi bi-calculator me-2"></i>Detalles</div>
                <span class="badge bg-white text-dark border">
                    <i class="bi bi-info-circle me-1"></i>${regla}
                </span>
            </div>

            <div class="mt-2 small">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Minutos totales</span>
                    <span class="fw-semibold">${minutosTotales} min</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Minutos restantes</span>
                    <span class="fw-semibold">${minutosRestantes} min</span>
                </div>
            </div>

            <hr class="my-2">

            <div class="small">
                ${linea1 || ''}
                ${linea2 || ''}
                ${lineaExtraBoleto}
                ${lineaSubtotal}
                ${lineaDescuento}
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted fw-bold">Total</span>
                    <span class="fw-bold">$${totalPagar.toFixed(2)}</span>
                </div>
            </div>

            ${linea3}
        </div>
    `;

    if (ui.bloqueDetalles) ui.bloqueDetalles.innerHTML = html;
}

function obtenerReglaAplicada(minutosTotales, tolerancia) {
    if (minutosTotales <= tolerancia) return `Gratis por tolerancia`;
    if (minutosTotales < 60) return `Cobro mínimo 1 hora`;
    return `Horas + fracción según tolerancia`;
}

function calcularCambio() {
    if (modoPantalla !== 'INGRESO') return;

    const recibido = parseFloat(ui.inputRecibido.value) || 0;
    const cambio = recibido - totalPagar;

    if (cambio >= 0) {
        ui.txtCambio.textContent = `$${cambio.toFixed(2)}`;
        ui.txtCambio.classList.remove('text-danger');
        ui.txtCambio.classList.add('text-success');
    } else {
        ui.txtCambio.textContent = `Faltan $${Math.abs(cambio).toFixed(2)}`;
        ui.txtCambio.classList.add('text-danger');
        ui.txtCambio.classList.remove('text-success');
    }

    validarBotonConfirmacion();
}

function validarBotonConfirmacion() {
    if (modoPantalla !== 'INGRESO' || !ingresoActual) {
        ui.btnConfirmar.disabled = true;
        return;
    }

    if (totalPagar === 0) {
        ui.btnConfirmar.disabled = false;
        return;
    }

    const recibido = parseFloat(ui.inputRecibido.value) || 0;
    ui.btnConfirmar.disabled = (recibido < totalPagar);
}

function sincronizarBarraAccion() {
    if (modoPantalla !== 'INGRESO' || !ingresoActual) {
        ui.barraAcciones.classList.remove('visible');
        return;
    }

    if (totalPagar === 0) {
        ui.barraAcciones.classList.add('visible');
        return;
    }

    const sucio =
        (ui.inputRecibido.value || '').trim().length > 0 ||
        !!ui.chkBoletoPerdido?.checked ||
        !!(ui.selDescuentoTipo?.value || '').trim() ||
        (ui.inputDescuentoValor && (ui.inputDescuentoValor.value || '').trim().length > 0) ||
        (ui.inputDescuentoMotivo && (ui.inputDescuentoMotivo.value || '').trim().length > 0);

    ui.barraAcciones.classList.toggle('visible', sucio);
}

async function registrarSalidaIngreso() {
    if (modoPantalla !== 'INGRESO' || !ingresoActual?.id) return;

    const idIngreso = parseInt(ingresoActual.id, 10);
    const recibido = parseFloat(ui.inputRecibido.value) || 0;
    const boletoPerdido = !!ui.chkBoletoPerdido?.checked;

    const tipo = (ui.selDescuentoTipo?.value || '').trim();
    const valor = parseFloat(ui.inputDescuentoValor?.value) || 0;
    const motivo = (ui.inputDescuentoMotivo?.value || '').trim().slice(0, 255);

    const confirmacion = await Swal.fire({
        title: '¿Confirmar Salida?',
        text: `Vehículo: ${ingresoActual.placa} - Total: $${totalPagar.toFixed(2)}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, registrar salida',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) return;

    try {
        const resp = await fetch(`${RUTA_CONTROLADOR}?accion=registrar_salida`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion: 'registrar_salida',
                id_ingreso: idIngreso,
                monto_recibido: (totalPagar === 0) ? 0 : recibido,
                boleto_perdido: boletoPerdido,
                descuento_tipo: tipo,
                descuento_valor: valor,
                descuento_motivo: motivo
            })
        });

        const data = await resp.json();
        if (!resp.ok || !data?.exito) {
            throw new Error(data?.mensaje || 'No fue posible registrar la salida.');
        }

        await Swal.fire({
            icon: 'success',
            title: '¡Salida Registrada!',
            text: data?.mensaje || 'OK',
            confirmButtonColor: '#4f46e5'
        });

        reiniciarInterfaz(true);

    } catch (e) {
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: e.message || 'No fue posible registrar la salida.',
            confirmButtonColor: '#4f46e5'
        });
    }
}

function reiniciarInterfaz(limpiarBusqueda = true) {
    if (limpiarBusqueda) ui.inputBusqueda.value = '';

    ui.panelCobro.style.display = 'none';
    ui.panelVacio.style.display = 'block';
    ui.barraAcciones.classList.remove('visible');

    modoPantalla = null;
    ingresoActual = null;
    pensionActual = null;
    calculoActual = null;

    totalBase = 0;
    extraBoletoPerdido = 0;
    subtotalActual = 0;
    totalPagar = 0;

    ocultarBoletoPerdido();
    ocultarDescuento();

    ui.inputRecibido.disabled = false;
    ui.inputRecibido.placeholder = "0.00";
    ui.inputRecibido.value = '';
    ui.txtCambio.textContent = '$0.00';
    ui.btnConfirmar.disabled = true;

    if (ui.bloqueDetalles) ui.bloqueDetalles.innerHTML = '';
    ocultarDetallesCobro();

    ui.inputBusqueda.focus();
}

/* ====== Helpers ====== */
function iniciarReloj() {
    const el = document.getElementById('reloj_sistema');
    if (!el) return;

    setInterval(() => {
        el.textContent = new Date().toLocaleTimeString('es-MX');
    }, 1000);
}

function formatearHoraDesdeMySQL(fechaMySQL) {
    if (!fechaMySQL) return '--:--';
    const fecha = new Date(fechaMySQL.replace(' ', 'T'));
    return fecha.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
}

function formatearFechaCortaDesdeMySQL(fechaMySQL) {
    if (!fechaMySQL) return '--/--/----';
    const fecha = new Date(fechaMySQL.replace(' ', 'T'));
    return fecha.toLocaleDateString('es-MX');
}

function formatearFechaSolo(fechaISO) {
    if (!fechaISO) return '--/--/----';
    const fecha = new Date(`${fechaISO}T00:00:00`);
    return fecha.toLocaleDateString('es-MX');
}

function mostrarError(msg) {
    Swal.fire({
        icon: 'error',
        title: 'Atención',
        text: msg,
        confirmButtonColor: '#4f46e5'
    });
}

function bloquearBotonBuscar(ocupado) {
    ui.btnBuscar.disabled = !!ocupado;
    ui.btnBuscar.innerHTML = ocupado
        ? '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando...'
        : '<i class="bi bi-search me-2"></i>Buscar';
}

function round2(n) {
    return Math.round((parseFloat(n) || 0) * 100) / 100;
}

/* ====== Helpers UI Detalles ====== */
function mostrarDetallesCobro() {
    if (ui.btnToggleDetalles) ui.btnToggleDetalles.style.display = '';
}

function ocultarDetallesCobro() {
    if (ui.btnToggleDetalles) ui.btnToggleDetalles.style.display = 'none';
    colapsarDetalles(true);
    if (ui.bloqueDetalles) ui.bloqueDetalles.innerHTML = '';
}

function colapsarDetalles(colapsar) {
    if (!ui.collapseDetalles) return;
    const inst = bootstrap.Collapse.getOrCreateInstance(ui.collapseDetalles, { toggle: false });
    if (colapsar) inst.hide(); else inst.show();
}
