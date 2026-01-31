document.addEventListener('DOMContentLoaded', () => {
    const ENDPOINT_ENTRADA = `${URL_BASE}app/controladores/EntradaControlador.php`;
    const ENDPOINT_CONFIG  = `${URL_BASE}app/controladores/ConfiguracionControlador.php`;
    const ENDPOINT_CSHARP  = `http://localhost:8080/imprimir/`;

    const form = document.getElementById('form_entrada');
    const inputPlaca = document.getElementById('placa');
    const toast = bootstrap.Toast.getOrCreateInstance(document.getElementById('notificacion_toast'));

    const chkPagoAdelantado = document.getElementById('chk_pago_adelantado');
    const inputPagoMonto = document.getElementById('pago_adelantado_monto');
    const selPagoConcepto = document.getElementById('pago_adelantado_concepto');
    const inputPagoNota = document.getElementById('pago_adelantado_nota');

    const setPagoAdelantadoUI = (activo) => {
        const on = !!activo;
        if (inputPagoMonto) { inputPagoMonto.disabled = !on; if (!on) inputPagoMonto.value = ''; }
        if (selPagoConcepto) { selPagoConcepto.disabled = !on; if (!on) selPagoConcepto.value = ''; }
        if (inputPagoNota) { inputPagoNota.disabled = !on; if (!on) inputPagoNota.value = ''; }
    };

    const mostrarToast = (msg, exito = true) => {
        document.getElementById('mensaje_toast').innerText = msg;
        document.getElementById('icono_toast').className = exito ? "bi bi-check-circle-fill text-success fs-4" : "bi bi-exclamation-triangle-fill text-danger fs-4";
        toast.show();
    };

    const cargarTarifas = async () => {
        try {
            const res = await fetch(`${ENDPOINT_CONFIG}?accion=obtener_datos`);
            const json = await res.json();
            if (json.exito) {
                document.getElementById('grid_tarifas').innerHTML = json.datos.tarifas.map((t, i) => `
                    <div class="col-6 col-md-3">
                        <input type="radio" class="btn-check" name="id_tarifa" id="t_${t.id}" value="${t.id}" ${i === 0 ? 'checked' : ''}>
                        <label class="tipo-vehiculo-card h-100" for="t_${t.id}"><i class="bi bi-car-front-fill fs-1 text-primary mb-2"></i><span class="fw-bold">${t.tipo_vehiculo}</span></label>
                    </div>`).join('');
            }
        } catch (e) { console.error(e); }
    };

    chkPagoAdelantado?.addEventListener('change', () => {
        setPagoAdelantadoUI(chkPagoAdelantado.checked);
        if (chkPagoAdelantado.checked) inputPagoMonto?.focus();
    });

    setPagoAdelantadoUI(false);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btn_registrar');
        btn.disabled = true;

        try {
            const resPHP = await fetch(`${ENDPOINT_ENTRADA}?accion=registrar_entrada`, { method: 'POST', body: new FormData(form) });
            const dataPHP = await resPHP.json();

            if (dataPHP.exito) {
                const resConf = await fetch(`${ENDPOINT_CONFIG}?accion=obtener_datos`);
                const jsonConf = await resConf.json();
                const c = jsonConf.datos.config;

                const estilos = JSON.parse(c.estilos_ticket || '[]');
                let comandos = [];

                estilos.forEach(item => {
                    let txt = "";
                    switch(item.id) {
                        case "p_nombre": if(c.ver_nombre == 1) txt = c.nombre_negocio.toUpperCase(); break;
                        case "p_telefono": if(c.ver_telefono == 1) txt = "TEL: " + c.telefono_negocio; break;
                        case "p_direccion": if(c.ver_direccion == 1) txt = c.direccion_fisica; break;
                        case "p_encabezado": if(c.ver_encabezado == 1) txt = c.encabezado_ticket; break;
                        case "p_cuerpo":
                            txt = `--------------------------\n` +
                                  `PLACA: ${inputPlaca.value.toUpperCase()}\n` +
                                  `MARCA: ${form.marca.value || 'N/A'} ${form.color.value || ''}\n` +
                                  `FOLIO: ${dataPHP.id_ingreso}\n` +
                                  `INGRESO: ${new Date().toLocaleString()}\n` +
                                  (chkPagoAdelantado?.checked && (parseFloat(inputPagoMonto?.value) || 0) > 0
                                      ? `PAGO ADEL.: $${(parseFloat(inputPagoMonto.value) || 0).toFixed(2)} ${selPagoConcepto?.value ? '(' + selPagoConcepto.value + ')' : ''}\n`
                                      : ``) +
                                  `--------------------------`;
                            break;
                        case "p_pie": if(c.ver_pie_e == 1) txt = c.pie_ticket_entrada; break;
                    }
                    if(txt) comandos.push({ Texto: txt, Alineacion: item.align, Size: parseInt(item.size) });
                });

                /* await fetch(ENDPOINT_CSHARP, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ Impresora: c.nombre_impresora, Ancho: parseInt(c.papel_ancho_mm), Avance: parseInt(c.avance_papel || 0), Bloques: comandos })
                }); */

                mostrarToast("Entrada registrada e impresa");
                form.reset();
                setPagoAdelantadoUI(false);
                if (chkPagoAdelantado) chkPagoAdelantado.checked = false;
                inputPlaca.focus();
            } else { mostrarToast(dataPHP.mensaje, false); }
        } catch (error) { mostrarToast("Error en el servidor", false); } finally { btn.disabled = false; }
    });

    cargarTarifas();
});
