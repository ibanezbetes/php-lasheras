/**
 * js/pedidos.js - Lógica del módulo de Pedidos (Maestro-Detalle)
 */

let lineasPedido = [];
let listaProductos = [];

function cargarDatosAuxiliares() {
    const promesas = [];

    if (listaProductos.length === 0) {
        promesas.push(
            fetch('CFrontal.php?controlador=Pedidos&metodo=getProductosJSON')
                .then(r => r.json())
                .then(data => { listaProductos = data; })
        );
    }
    return Promise.all(promesas);
}

function generarOpcionesProductos() {
    let optsProductos = '<option value="">Seleccionar Producto...</option>';
    listaProductos.forEach(p => {
        optsProductos += `<option value="${p.idProducto}" data-precio="${p.precioVenta}">${p.producto} (${p.precioVenta}€)</option>`;
    });
    return optsProductos;
}

function buscarPedidos(pagina = 1, tamPag = 5) {
    const params = `pagina=${pagina}&tam_pag=${tamPag}`;
    buscar("Pedidos", "getVistaListadoPedidos", "formularioBuscarPedido", "capaResultadosPedidos", params);
}

function verTodosPedidos() {
    document.getElementById("formularioBuscarPedido").reset();
    buscar("Pedidos", "getVistaListadoPedidos", "formularioBuscarPedido", "capaResultadosPedidos");
}

function mostrarFormularioPedido() {
    lineasPedido = [];
    cargarDatosAuxiliares().then(() => {
        renderFormularioPedido();
    });
}

function renderFormularioPedido(pedido = null) {
    const esEdicion = pedido !== null;
    const titulo = esEdicion ? 'Editar Pedido #' + pedido.idPedido : 'Nuevo Pedido';

    const usuarioNombreInicial = esEdicion ? `${pedido.u_nombre} ${pedido.u_apellido1}` : '';

    let lineasHTML = '';
    let total = 0;
    lineasPedido.forEach((linea, index) => {
        const subtotal = linea.cantidad * linea.precioUnitario;
        total += subtotal;
        lineasHTML += `
            <tr>
                <td>${linea.producto || 'Producto #' + linea.idProducto}</td>
                <td>${linea.cantidad}</td>
                <td>${parseFloat(linea.precioUnitario).toFixed(2)} €</td>
                <td>${subtotal.toFixed(2)} €</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeLinea(${index});">❌</button></td>
            </tr>
        `;
    });

    const formulario = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;"></div>
        
        <div class="p-4 rounded shadow" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70%; min-width: 600px; max-height: 90vh; overflow-y: auto; z-index: 1050; background-color: var(--surface-color); color: var(--bs-body-color); border: 1px solid var(--bs-border-color);">
            <h4 class="mb-4" style="color: var(--bs-light) !important;">${titulo}</h4>
            <div id="mensajesPedido"></div>
            
            <form id="formPedido">
                ${esEdicion ? `<input type="hidden" id="idPedidoEdit" value="${pedido.idPedido}">` : ''}
                
                <div class="row mb-3">
                    <div class="col-md-4 position-relative">
                        <label class="form-label text-white">Usuario *</label>
                        <input type="text" class="form-control" id="pedidoUsuarioNombre" placeholder="Buscar usuario..." autocomplete="off" value="${usuarioNombreInicial}" required>
                        <input type="hidden" id="pedidoUsuario" value="${esEdicion ? pedido.idUsuario : ''}">
                        <div id="sugerenciasUsuarios" class="list-group position-absolute w-100" style="z-index: 1051; max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white">Fecha Pedido *</label>
                        <input type="datetime-local" class="form-control" id="pedidoFechaPedido" value="${esEdicion ? formatDateTime(pedido.fechaPedido) : formatDateTime(new Date())}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white">Fecha Almacén</label>
                        <input type="datetime-local" class="form-control" id="pedidoFechaAlmacen" value="${esEdicion && pedido.fechaAlmacen != '0000-00-00 00:00:00' ? formatDateTime(pedido.fechaAlmacen) : ''}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-white">Fecha Envío</label>
                        <input type="datetime-local" class="form-control" id="pedidoFechaEnvio" value="${esEdicion && pedido.fechaEnvio != '0000-00-00 00:00:00' ? formatDateTime(pedido.fechaEnvio) : ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white">Fecha Recibido</label>
                        <input type="datetime-local" class="form-control" id="pedidoFechaRecibido" value="${esEdicion && pedido.fechaRecibido != '0000-00-00 00:00:00' ? formatDateTime(pedido.fechaRecibido) : ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white">Fecha Finalizado</label>
                        <input type="datetime-local" class="form-control" id="pedidoFechaFinalizado" value="${esEdicion && pedido.fechaFinalizado != '0000-00-00 00:00:00' ? formatDateTime(pedido.fechaFinalizado) : ''}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-white">Transporte</label>
                        <input type="text" class="form-control" id="pedidoTransporte" value="${esEdicion ? (pedido.transporte || '') : ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white">Dirección</label>
                        <input type="text" class="form-control" id="pedidoDireccion" value="${esEdicion ? (pedido.direccion || '') : ''}">
                    </div>
                </div>

                <div class="card mb-3 bg-dark border-secondary">
                    <div class="card-header text-white border-secondary"><strong>Añadir Producto</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <select class="form-control" id="lineaProducto" 
                                        onchange="document.getElementById('lineaPrecio').value = this.options[this.selectedIndex].dataset.precio || '';">
                                    ${generarOpcionesProductos()}
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" id="lineaCantidad" placeholder="Cant." min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" class="form-control" id="lineaPrecio" placeholder="Precio" readonly>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-success w-100" onclick="addLinea();">➕ Añadir</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-sm table-striped text-white" id="tablaLineas">
                        <thead><tr class="text-white">
                            <th class="text-white">Producto</th><th class="text-white">Cantidad</th><th class="text-white">Precio</th><th class="text-white">Subtotal</th><th></th>
                        </tr></thead>
                        <tbody class="text-white">${lineasHTML}</tbody>
                        <tfoot class="text-white">
                            <tr><td colspan="3" class="text-end text-white"><strong>Total:</strong></td>
                            <td colspan="2" class="text-white"><strong>${total.toFixed(2)} €</strong></td></tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <button type="button" class="btn btn-primary" onclick="guardarPedido();">
                        ${esEdicion ? '✏️ Actualizar' : '💾 Guardar'}
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cancelarFormularioPedido();">Cancelar</button>
                </div>
            </form>
        </div>
    `;

    document.getElementById("capaPedidoFormulario").innerHTML = formulario;
    document.getElementById("capaPedidoFormulario").style.display = "block";
    
    inicializarAutocompleteUsuario();
}

function formatDateTime(dateInput) {
    if (!dateInput) return "";
    let d = new Date(dateInput);
    if (isNaN(d.getTime())) return "";
    let month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear(),
        hours = '' + d.getHours(),
        minutes = '' + d.getMinutes();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    if (hours.length < 2) hours = '0' + hours;
    if (minutes.length < 2) minutes = '0' + minutes;

    return [year, month, day].join('-') + 'T' + [hours, minutes].join(':');
}

function unformatDateTime(datetimeLocal) {
    if (!datetimeLocal) return "";
    return datetimeLocal.replace('T', ' ') + ':00';
}

function inicializarAutocompleteUsuario() {
    const inputNombre = document.getElementById('pedidoUsuarioNombre');
    const inputOculto = document.getElementById('pedidoUsuario');
    const capaSugerencias = document.getElementById('sugerenciasUsuarios');
    
    if (!inputNombre) return;

    let timeoutSugerencias;
    
    inputNombre.addEventListener('input', function() {
        const filtro = this.value.trim();
        inputOculto.value = ""; 
        
        clearTimeout(timeoutSugerencias);
        
        if (filtro.length < 2) {
            capaSugerencias.style.display = 'none';
            return;
        }
        
        timeoutSugerencias = setTimeout(() => {
            fetch(`CFrontal.php?controlador=Pedidos&metodo=buscarUsuariosJSON&filtro=${encodeURIComponent(filtro)}`)
            .then(r => r.json())
            .then(usuarios => {
                capaSugerencias.innerHTML = '';
                if (usuarios.length > 0) {
                    usuarios.forEach(u => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action bg-dark text-white border-secondary text-start';
                        btn.textContent = `${u.nombre} ${u.apellido1}`;
                        btn.onclick = () => {
                            inputNombre.value = `${u.nombre} ${u.apellido1}`;
                            inputOculto.value = u.idUsuario;
                            capaSugerencias.style.display = 'none';
                        };
                        capaSugerencias.appendChild(btn);
                    });
                    capaSugerencias.style.display = 'block';
                } else {
                    capaSugerencias.innerHTML = '<div class="list-group-item bg-dark text-muted border-secondary text-start">No hay coincidencias</div>';
                    capaSugerencias.style.display = 'block';
                }
            });
        }, 300);
    });
    
    document.addEventListener('click', function(e) {
        if (e.target !== inputNombre && e.target.parentElement !== capaSugerencias) {
            capaSugerencias.style.display = 'none';
        }
    });
}

function addLinea() {
    const select = document.getElementById('lineaProducto');
    const idProducto = select.value;
    const producto = select.options[select.selectedIndex].text;
    const cantidad = parseInt(document.getElementById('lineaCantidad').value);
    const precio = parseFloat(document.getElementById('lineaPrecio').value);

    if (!idProducto || !cantidad || !precio) {
        alert("Complete todos los campos de la línea");
        return;
    }

    lineasPedido.push({
        idProducto: idProducto,
        producto: producto,
        cantidad: cantidad,
        precioUnitario: precio
    });

    actualizarRenderActual();
}

function removeLinea(index) {
    lineasPedido.splice(index, 1);
    actualizarRenderActual();
}

function actualizarRenderActual() {
    const pedidoActual = document.getElementById('idPedidoEdit');
    const mockPedido = {
        idPedido: pedidoActual ? pedidoActual.value : null,
        idUsuario: document.getElementById('pedidoUsuario').value,
        u_nombre: document.getElementById('pedidoUsuarioNombre').value.split(' ')[0] || '',
        u_apellido1: document.getElementById('pedidoUsuarioNombre').value.split(' ').slice(1).join(' ') || '',
        fechaPedido: unformatDateTime(document.getElementById('pedidoFechaPedido').value),
        fechaAlmacen: unformatDateTime(document.getElementById('pedidoFechaAlmacen').value),
        fechaEnvio: unformatDateTime(document.getElementById('pedidoFechaEnvio').value),
        fechaRecibido: unformatDateTime(document.getElementById('pedidoFechaRecibido').value),
        fechaFinalizado: unformatDateTime(document.getElementById('pedidoFechaFinalizado').value),
        transporte: document.getElementById('pedidoTransporte').value,
        direccion: document.getElementById('pedidoDireccion').value
    };
    renderFormularioPedido(mockPedido);
}

function guardarPedido() {
    const idUsuario = document.getElementById('pedidoUsuario').value;
    const fechaPedido = document.getElementById('pedidoFechaPedido').value;
    const fechaAlmacen = document.getElementById('pedidoFechaAlmacen').value;
    const fechaEnvio = document.getElementById('pedidoFechaEnvio').value;
    const fechaRecibido = document.getElementById('pedidoFechaRecibido').value;
    const fechaFinalizado = document.getElementById('pedidoFechaFinalizado').value;
    const transporte = document.getElementById('pedidoTransporte').value;
    const direccion = document.getElementById('pedidoDireccion').value;
    const idPedidoEdit = document.getElementById('idPedidoEdit');

    if (!idUsuario || !fechaPedido) {
        alert("Seleccione un usuario y una fecha de pedido");
        return;
    }

    if (lineasPedido.length === 0) {
        alert("Debe añadir al menos un producto al pedido");
        return;
    }

    const datos = new URLSearchParams({
        controlador: 'Pedidos',
        metodo: idPedidoEdit ? 'actualizarPedido' : 'crearPedido',
        idUsuario: idUsuario,
        fechaPedido: unformatDateTime(fechaPedido),
        fechaAlmacen: unformatDateTime(fechaAlmacen),
        fechaEnvio: unformatDateTime(fechaEnvio),
        fechaRecibido: unformatDateTime(fechaRecibido),
        fechaFinalizado: unformatDateTime(fechaFinalizado),
        transporte: transporte,
        direccion: direccion,
        detalles: JSON.stringify(lineasPedido)
    });

    if (idPedidoEdit) {
        datos.append('idPedido', idPedidoEdit.value);
    }

    fetch("CFrontal.php", {
        method: "POST",
        body: datos
    })
    .then(r => r.text())
    .then(res => {
        if (res.includes('exitosamente')) {
            alert(idPedidoEdit ? "Pedido actualizado correctamente" : "Pedido creado correctamente");
            cancelarFormularioPedido();
            verTodosPedidos();
        } else {
            alert("Error: " + res);
        }
    });
}

function editarPedido(idPedido) {
    cargarDatosAuxiliares().then(() => {
        fetch(`CFrontal.php?controlador=Pedidos&metodo=obtenerPedido&idPedido=${idPedido}`)
        .then(r => r.json())
        .then(pedido => {
            if (pedido.error) {
                alert(pedido.error);
            } else {
                lineasPedido = pedido.detalles.map(d => ({
                    idProducto: d.idProducto,
                    producto: d.producto,
                    cantidad: parseInt(d.cantidad),
                    precioUnitario: parseFloat(d.precioUnitario)
                }));
                renderFormularioPedido(pedido);
            }
        });
    });
}

function verDetallesPedido(idPedido) {
    fetch(`CFrontal.php?controlador=Pedidos&metodo=obtenerPedido&idPedido=${idPedido}`)
    .then(r => r.json())
    .then(pedido => {
        if (pedido.error) {
            alert(pedido.error);
        } else {
            renderModalDetallesPedido(pedido);
        }
    });
}

function renderModalDetallesPedido(pedido) {
    let lineasHTML = '';
    let total = 0;
    
    pedido.detalles.forEach(linea => {
        const subtotal = linea.cantidad * linea.precioUnitario;
        total += subtotal;
        lineasHTML += `
            <tr>
                <td>${linea.producto || 'Producto #' + linea.idProducto}</td>
                <td>${linea.cantidad}</td>
                <td>${parseFloat(linea.precioUnitario).toFixed(2)} €</td>
                <td>${subtotal.toFixed(2)} €</td>
            </tr>
        `;
    });

    const estadoTexto = pedido.estado === 'C' ? 'Completado' : 'Pendiente';
    const badgeEstado = pedido.estado === 'C' ? '<span class="badge bg-success">Completado</span>' : '<span class="badge bg-warning text-dark">Pendiente</span>';

    const formulario = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;" onclick="cancelarFormularioPedido();"></div>
        
        <div class="p-4 rounded shadow" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70%; min-width: 600px; max-height: 90vh; overflow-y: auto; z-index: 1050; background-color: var(--surface-color); color: var(--bs-body-color); border: 1px solid var(--bs-border-color);">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h4 class="m-0" style="color: var(--bs-light) !important;">
                    🏷️ Detalles del Pedido #${pedido.idPedido}
                </h4>
                <button type="button" class="btn-close btn-close-white" onclick="cancelarFormularioPedido();"></button>
            </div>
            
            <div class="row mb-4 bg-dark p-3 rounded border border-secondary">
                <div class="col-md-6 mb-2">
                    <p class="text-white mb-2"><strong class="text-info">👤 Cliente:</strong><br> ${pedido.u_nombre} ${pedido.u_apellido1}</p>
                </div>
                <div class="col-md-3 mb-2">
                    <p class="text-white mb-2"><strong class="text-info">📅 Fecha:</strong><br> ${pedido.fechaPedido}</p>
                </div>
                <div class="col-md-3 mb-2">
                    <p class="text-white mb-2"><strong class="text-info">📋 Estado:</strong><br> ${badgeEstado}</p>
                </div>
                <div class="col-md-6 mt-2">
                    <p class="text-white mb-2"><strong class="text-info">🏠 Dirección:</strong><br> ${pedido.direccion || '-'}</p>
                </div>
                <div class="col-md-6 mt-2">
                    <p class="text-white mb-2"><strong class="text-info">🚚 Transporte:</strong><br> ${pedido.transporte || '-'}</p>
                </div>
            </div>

            <h5 class="text-white mb-3">📄 Productos Incluidos</h5>
            <div class="table-responsive mb-3 border border-secondary rounded overflow-hidden">
                <table class="table table-sm table-dark table-striped text-white m-0">
                    <thead><tr>
                        <th class="ps-3 py-2">Producto</th><th class="py-2">Cantidad</th><th class="py-2">Precio U.</th><th class="text-end pe-3 py-2">Subtotal</th>
                    </tr></thead>
                    <tbody>${lineasHTML}</tbody>
                    <tfoot class="border-top border-secondary bg-dark text-white">
                        <tr>
                            <td colspan="3" class="text-end py-3"><strong>Total del Pedido:</strong></td>
                            <td class="text-end pe-3 py-3"><strong class="text-success fs-5">${total.toFixed(2)} €</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top border-secondary">
                <button type="button" class="btn btn-primary" onclick="editarPedido(${pedido.idPedido});">
                    ✏️ Editar Pedido
                </button>
                <button type="button" class="btn btn-secondary" onclick="cancelarFormularioPedido();">
                    Cerrar
                </button>
            </div>
        </div>
    `;

    document.getElementById("capaPedidoFormulario").innerHTML = formulario;
    document.getElementById("capaPedidoFormulario").style.display = "block";
}

function eliminarPedido(idPedido) {
    if (!confirm('¿Está seguro de eliminar este pedido?')) return;

    fetch("CFrontal.php", {
        method: "POST",
        body: new URLSearchParams({
            controlador: 'Pedidos',
            metodo: 'eliminarPedido',
            idPedido: idPedido
        })
    })
    .then(r => r.text())
    .then(res => {
        if (res.includes('exitosamente')) {
            alert("Pedido eliminado correctamente");
            verTodosPedidos();
        } else {
            alert("Error al eliminar: " + res);
        }
    });
}

function cancelarFormularioPedido() {
    document.getElementById("capaPedidoFormulario").innerHTML = "";
    document.getElementById("capaPedidoFormulario").style.display = "none";
    lineasPedido = [];
}
