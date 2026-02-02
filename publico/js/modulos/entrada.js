// Archivo: publico/js/modulos/entrada.js

document.addEventListener('DOMContentLoaded', () => {
    const ENDPOINT_ENTRADA = `${URL_BASE}app/controladores/EntradaControlador.php`;
    const ENDPOINT_CONFIG  = `${URL_BASE}app/controladores/ConfiguracionControlador.php`;
    const ENDPOINT_CSHARP  = `http://localhost:8080/imprimir/`;

    const form = document.getElementById('form_entrada');
    const inputPlaca = document.getElementById('placa');

    const toastEl = document.getElementById('notificacion_toast');
    const toast = toastEl ? bootstrap.Toast.getOrCreateInstance(toastEl) : null;

    const chkPagoAdelantado = document.getElementById('chk_pago_adelantado');
    const inputPagoMonto = document.getElementById('pago_adelantado_monto');
    const selPagoConcepto = document.getElementById('pago_adelantado_concepto');
    const inputPagoNota = document.getElementById('pago_adelantado_nota');

    if (!form) return;

    const mostrarToast = (msg, exito = true) => {
        const elMsg = document.getElementById('mensaje_toast');
        const elIcon = document.getElementById('icono_toast');

        if (elMsg) elMsg.innerText = msg || '';
        if (elIcon) {
            elIcon.className = exito
                ? "bi bi-check-circle-fill text-success fs-4"
                : "bi bi-exclamation-triangle-fill text-danger fs-4";
        }

        if (toast) toast.show();
    };

    const setPagoAdelantadoUI = (activo) => {
        const on = !!activo;

        if (inputPagoMonto) {
            inputPagoMonto.disabled = !on;
            if (!on) inputPagoMonto.value = '';
        }
        if (selPagoConcepto) {
            selPagoConcepto.disabled = !on;
            if (!on) selPagoConcepto.value = '';
        }
        if (inputPagoNota) {
            inputPagoNota.disabled = !on;
            if (!on) inputPagoNota.value = '';
        }
    };

    const obtenerTarifaSeleccionada = () => {
        const el = document.querySelector('input[name="id_tarifa"]:checked');
        return el ? el.value : null;
    };

    const calcularPagoAdelantado = async () => {
        if (!chkPagoAdelantado || !chkPagoAdelantado.checked) return;
        if (!inputPagoMonto || !selPagoConcepto) return;

        const concepto = (selPagoConcepto.value || '').trim();
        const idTarifa = obtenerTarifaSeleccionada();

        // OTRO o vacío: manual, no forzar
        if (!concepto || concepto === 'OTRO' || !idTarifa) return;

        try {
            const url = `${ENDPOINT_ENTRADA}?accion=calcular_adelanto&id_tarifa=${encodeURIComponent(idTarifa)}&concepto=${encodeURIComponent(concepto)}`;
            const res = await fetch(url);
            const json = await res.json();

            // Soporta {exito:true, datos:{monto_sugerido:...}} o {ok:true,...}
            const ok = (json && (json.exito === true || json.ok === true));
            const datos = json?.datos || {};
            if (!ok) return;

            const m = parseFloat(datos.monto_sugerido ?? datos.monto ?? 0);
            inputPagoMonto.value = (isFinite(m) ? m : 0).toFixed(2);
        } catch (e) {
            // silencioso: operador puede capturar manualmente
        }
    };

    const cargarTarifas = async () => {
        try {
            const res = await fetch(`${ENDPOINT_CONFIG}?accion=obtener_datos`);
            const json = await res.json();
            if (json.exito && json.datos && Array.isArray(json.datos.tarifas)) {
                const cont = document.getElementById('grid_tarifas');
                if (!cont) return;

                cont.innerHTML = json.datos.tarifas.map((t, i) => `
                    <div class="col-6 col-md-3">
                        <input type="radio" class="btn-check" name="id_tarifa" id="t_${t.id}" value="${t.id}" ${i === 0 ? 'checked' : ''}>
                        <label class="tipo-vehiculo-card h-100" for="t_${t.id}">
                            <i class="bi bi-car-front-fill fs-1 text-primary mb-2"></i>
                            <span class="fw-bold">${t.tipo_vehiculo}</span>
                        </label>
                    </div>
                `).join('');
            }
        } catch (e) {
            console.error(e);
        }
    };

    chkPagoAdelantado?.addEventListener('change', async () => {
        setPagoAdelantadoUI(chkPagoAdelantado.checked);
        if (chkPagoAdelantado.checked) {
            inputPagoMonto?.focus();
            await calcularPagoAdelantado();
        }
    });

    selPagoConcepto?.addEventListener('change', async () => {
        await calcularPagoAdelantado();
    });

    document.addEventListener('change', async (ev) => {
        const t = ev.target;
        if (t && t.matches && t.matches('input[name="id_tarifa"]')) {
            await calcularPagoAdelantado();
        }
    });

    setPagoAdelantadoUI(false);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btn_registrar');
        if (btn) btn.disabled = true;

        try {
            const resPHP = await fetch(`${ENDPOINT_ENTRADA}?accion=registrar_entrada`, {
                method: 'POST',
                body: new FormData(form)
            });
            const dataPHP = await resPHP.json();

            if (dataPHP.exito) {
                const resConf = await fetch(`${ENDPOINT_CONFIG}?accion=obtener_datos`);
                const jsonConf = await resConf.json();
                const c = jsonConf?.datos?.config || {};
                const moneda = c.moneda_simbolo || '$';

                const estilos = JSON.parse(c.estilos_ticket || '[]');
                let comandos = [];

                estilos.forEach(item => {
                    let txt = "";
                    switch (item.id) {
                        case "p_nombre":
                            if (c.ver_nombre == 1) txt = (c.nombre_negocio || "").toUpperCase();
                            break;
                        case "p_telefono":
                            if (c.ver_telefono == 1) txt = "TEL: " + (c.telefono_negocio || "");
                            break;
                        case "p_direccion":
                            if (c.ver_direccion == 1) txt = (c.direccion_fisica || "");
                            break;
                        case "p_encabezado":
                            if (c.ver_encabezado == 1) txt = (c.encabezado_ticket || "");
                            break;
                        case "p_cuerpo":
                            txt =
                                `--------------------------\n` +
                                `PLACA: ${(inputPlaca?.value || "").toUpperCase()}\n` +
                                `MARCA: ${(form.marca?.value || 'N/A')} ${(form.color?.value || '')}\n` +
                                `FOLIO: ${dataPHP.id_ingreso}\n` +
                                `INGRESO: ${new Date().toLocaleString()}\n` +
                                (
                                    chkPagoAdelantado?.checked && (parseFloat(inputPagoMonto?.value) || 0) > 0
                                        ? `PAGO ADEL.: ${moneda}${(parseFloat(inputPagoMonto.value) || 0).toFixed(2)} ${selPagoConcepto?.value ? '(' + selPagoConcepto.value + ')' : ''}\n`
                                        : ``
                                ) +
                                `--------------------------`;
                            break;
                        case "p_pie":
                            if (c.ver_pie_e == 1) txt = (c.pie_ticket_entrada || "");
                            break;
                        default:
                            break;
                    }

                    if (txt) {
                        comandos.push({
                            Texto: txt,
                            Alineacion: item.align,
                            Size: parseInt(item.size)
                        });
                    }
                });

                /* Si lo usas, descomenta:
                await fetch(ENDPOINT_CSHARP, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        Impresora: c.nombre_impresora,
                        Ancho: parseInt(c.papel_ancho_mm),
                        Avance: parseInt(c.avance_papel || 0),
                        Bloques: comandos
                    })
                });
                */

                mostrarToast("✅ Entrada registrada e impresa", true);
                form.reset();
                setPagoAdelantadoUI(false);
                if (chkPagoAdelantado) chkPagoAdelantado.checked = false;
                inputPlaca?.focus();
            } else {
                mostrarToast("⚠️ " + (dataPHP.mensaje || "Error"), false);
            }
        } catch (error) {
            mostrarToast("❌ Error de red o servidor", false);
        } finally {
            if (btn) btn.disabled = false;
        }
    });

    cargarTarifas();
    inputPlaca?.focus();
});
