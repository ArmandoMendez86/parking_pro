document.addEventListener('DOMContentLoaded', () => {
    const ENDPOINT = `${URL_BASE}app/controladores/ConfiguracionControlador.php`;
    const form = document.getElementById('form_configuracion');
    const barra = document.getElementById('barra_guardado');
    const inputEstilos = document.getElementById('estilos_ticket_input');
    const contenedor = document.getElementById('contenedor_orden');
    const mockup = document.getElementById('ticket_mockup');
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(document.getElementById('notificacion_toast'));

    const aplicarEstiloVisual = (el, align, size) => {
        const valDiv = el.querySelector('.val-block');
        if (align) {
            el.style.textAlign = align;
            if (valDiv) {
                valDiv.style.textAlign = align;
                valDiv.classList.remove('text-center', 'text-start', 'text-end');
                const cssClass = align === 'center' ? 'text-center' : (align === 'left' ? 'text-start' : 'text-end');
                valDiv.classList.add(cssClass);
            }
        }
        if (size && valDiv) valDiv.style.fontSize = size + "px";
    };

    window.setAlign = (id, align) => {
        const el = document.querySelector(`[data-id="${id}"]`);
        if (el) { el.setAttribute('data-align', align); aplicarEstiloVisual(el, align, null); capturarTodo(); }
    };

    window.setSize = (id, delta) => {
        const el = document.querySelector(`[data-id="${id}"]`);
        if (el) {
            let s = parseInt(el.getAttribute('data-size')) || 10;
            s += delta; el.setAttribute('data-size', s); aplicarEstiloVisual(el, null, s); capturarTodo();
        }
    };

    const capturarTodo = () => {
        if (!contenedor) return;
        const config = [...contenedor.querySelectorAll('.sortable-item')].map(item => ({
            id: item.dataset.id,
            align: item.getAttribute('data-align') || 'center',
            size: item.getAttribute('data-size') || '10'
        }));
        inputEstilos.value = JSON.stringify(config);
        if (barra) barra.classList.add('visible');
    };

    const actualizarPreview = () => {
        const getV = (id) => document.getElementById(id)?.value || "";
        const getC = (id) => document.getElementById(id)?.checked || false;

        // PARCHE: Ancho de papel en tiempo real
        if (mockup) {
            const ancho = getV('papel_tipo');
            mockup.classList.remove('papel-58', 'papel-80');
            mockup.classList.add(`papel-${ancho || '80'}`);
        }

        if (document.getElementById('p_nombre_val')) document.getElementById('p_nombre_val').innerText = getV('nombre_negocio').toUpperCase();
        if (document.getElementById('p_telefono_val')) document.getElementById('p_telefono_val').innerText = "Tel: " + getV('telefono_negocio');
        if (document.getElementById('p_direccion_val')) document.getElementById('p_direccion_val').innerText = getV('direccion');
        if (document.getElementById('p_encabezado_val')) document.getElementById('p_encabezado_val').innerText = getV('encabezado_global');
        if (document.getElementById('p_pie_val')) document.getElementById('p_pie_val').innerText = getV('pie_entrada');

        const toggle = (id, vis) => { const el = contenedor?.querySelector(`[data-id="${id}"]`); if (el) el.style.display = vis ? 'block' : 'none'; };
        toggle('p_nombre', getC('ver_nombre'));
        toggle('p_telefono', getC('ver_telefono'));
        toggle('p_direccion', getC('ver_direccion'));
        toggle('p_encabezado', getC('ver_encabezado'));
        toggle('p_pie', getC('ver_pie_e'));
    };

    const cargarDatos = async () => {
        try {
            const res = await fetch(`${ENDPOINT}?accion=obtener_datos`);
            const json = await res.json();
            const c = json.datos.config;

            const fill = (id, v) => { if (document.getElementById(id)) document.getElementById(id).value = v || ""; };
            const check = (id, v) => { if (document.getElementById(id)) document.getElementById(id).checked = (v == 1); };

            fill('nombre_negocio', c.nombre_negocio); fill('telefono_negocio', c.telefono_negocio);
            fill('direccion', c.direccion_fisica); fill('moneda', c.moneda_simbolo);
            fill('tolerancia_entrada', c.tolerancia_entrada_minutos); fill('nombre_impresora', c.nombre_impresora);
            fill('papel_tipo', c.papel_ancho_mm); fill('copias', c.numero_copias);
            fill('encabezado_global', c.encabezado_ticket); fill('pie_entrada', c.pie_ticket_entrada);
            fill('pie_salida', c.pie_ticket_salida);

            check('ver_nombre', c.ver_nombre); check('ver_telefono', c.ver_telefono);
            check('ver_direccion', c.ver_direccion); check('ver_encabezado', c.ver_encabezado);
            check('ver_pie_e', c.ver_pie_e); check('ver_pie_s', c.ver_pie_s);

            if (c.estilos_ticket && contenedor) {
                const estilos = JSON.parse(c.estilos_ticket);
                estilos.forEach(item => {
                    const el = contenedor.querySelector(`[data-id="${item.id}"]`);
                    if (el) {
                        el.setAttribute('data-align', item.align); el.setAttribute('data-size', item.size);
                        aplicarEstiloVisual(el, item.align, item.size); contenedor.appendChild(el);
                    }
                });
                inputEstilos.value = c.estilos_ticket;
            }

            document.getElementById('lista_horarios').innerHTML = json.datos.horarios.map((h, i) => `
                <tr><td class="small fw-bold">${h.dia_semana}<input type="hidden" name="dia_nombre[${i}]" value="${h.dia_semana}"></td>
                <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="dia_activo[${i}]" ${h.esta_abierto==1?'checked':''}></div></td>
                <td><input type="time" name="abre[${i}]" class="form-control form-control-sm" value="${h.hora_apertura}"></td>
                <td><input type="time" name="cierra[${i}]" class="form-control form-control-sm" value="${h.hora_cierre}"></td></tr>`).join('');

            document.getElementById('cuerpo_tarifas').innerHTML = '';
            // PARCHE: Pasar el ID a la función agregarTarifa
            json.datos.tarifas.forEach(t => agregarTarifa(t.tipo_vehiculo, t.costo_hora, t.costo_fraccion_extra, t.tolerancia_extra_minutos, t.costo_boleto_perdido, t.id));

            actualizarPreview(); initDragDrop();
            form.addEventListener('input', () => { actualizarPreview(); barra.classList.add('visible'); });

            // PARCHE: Escuchar cambio de papel específicamente
            document.getElementById('papel_tipo')?.addEventListener('change', actualizarPreview);

        } catch (e) { console.error(e); }
    };

    window.agregarTarifa = (tipo='', costo=0, extra=0, tol=0, perd=0, id=null) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input type="hidden" name="t_id[]" value="${id || ''}">
                <input type="text" name="t_nombre[]" class="form-control form-control-sm" value="${tipo}">
            </td>
            <td><input type="number" step="0.01" name="t_hora[]" class="form-control form-control-sm" value="${costo}"></td>
            <td><input type="number" step="0.01" name="t_extra[]" class="form-control form-control-sm" value="${extra}"></td>
            <td><input type="number" name="t_tol_extra[]" class="form-control form-control-sm" value="${tol}"></td>
            <td><input type="number" step="0.01" name="t_perdido[]" class="form-control form-control-sm" value="${perd}"></td>
            <td><button type="button" class="btn btn-sm btn-link text-danger btn-borrar"><i class="bi bi-trash"></i></button></td>`;

        // ✅ BORRADO REAL: marcar para borrar en BD si ya tiene ID
        tr.querySelector('.btn-borrar').addEventListener('click', () => {
            const idInput = tr.querySelector('input[name="t_id[]"]');
            const id = idInput ? idInput.value : '';
            // Si ya existe en BD, márcala para borrar al guardar
            if (id && id.trim() !== '') {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 't_borrar[]';
                hidden.value = id;
                form.appendChild(hidden);
            }
            tr.remove();
            if (barra) barra.classList.add('visible');
        });

        document.getElementById('cuerpo_tarifas').appendChild(tr);
    };

    // ✅ FIX: Conectar el botón "+ Añadir Tarifa" de la vista a agregarTarifa()
    document.getElementById('btn_add_tarifa')?.addEventListener('click', () => {
        window.agregarTarifa('', 0, 0, 0, 0, null);
        if (barra) barra.classList.add('visible');
    });

    const initDragDrop = () => {
        let dragItem = null;
        document.querySelectorAll('.sortable-item').forEach(item => {
            item.addEventListener('dragstart', () => { dragItem = item; item.classList.add('dragging'); });
            item.addEventListener('dragend', () => { item.classList.remove('dragging'); capturarTodo(); });
        });
        contenedor.addEventListener('dragover', e => {
            e.preventDefault();
            const after = [...contenedor.querySelectorAll('.sortable-item:not(.dragging)')].reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = e.clientY - box.top - box.height / 2;
                return (offset < 0 && offset > closest.offset) ? { offset, element: child } : closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
            if (after == null) contenedor.appendChild(dragItem); else contenedor.insertBefore(dragItem, after);
        });
    };

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const res = await fetch(`${ENDPOINT}?accion=guardar_todo`, { method: 'POST', body: new FormData(form) });
        const data = await res.json();
        if (data.exito) {
            barra.classList.remove('visible');
            document.getElementById('mensaje_toast').innerText = data.mensaje;
            toastBootstrap.show();
        } else {
            alert("Error: " + data.mensaje);
        }
    });

    cargarDatos();
});
