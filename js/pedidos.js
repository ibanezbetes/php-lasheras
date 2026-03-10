/**
 * js/pedidos.js - Lógica del módulo de Pedidos (lado cliente)
 * 
 * Gestiona las operaciones CRUD de pedidos (maestro-detalle) desde el navegador:
 * - Buscar y listar pedidos (con paginación)
 * - Crear nuevos pedidos con líneas de detalle (modal)
 * - Editar pedidos existentes (cargando sus líneas actuales)
 * - Eliminar pedidos previa confirmación
 * 
 * El formulario permite:
 * - Seleccionar usuario y fecha
 * - Añadir/quitar líneas de detalle (producto, cantidad, precio)
 * - Ver el total del pedido actualizado en tiempo real
 * 
 * Depende de: utils.js (buscar, mostrarError, mostrarExito)
 */

// =====================================================================
// VARIABLES GLOBALES
// =====================================================================

/** @type {Array} Líneas de detalle del pedido en edición/creación */
let lineasPedido = [];

/** @type {Array} Lista de usuarios activos (precargada del servidor) */
let listaUsuarios = [];

/** @type {Array} Lista de productos activos (precargada del servidor) */
let listaProductos = [];

// =====================================================================
// CARGA DE DATOS AUXILIARES
// =====================================================================

/**
 * Cargar listas de usuarios y productos del servidor
 * Se llama antes de mostrar el formulario para poblar los selects.
 * Los datos se cachean en las variables globales para no repetir peticiones.
 * 
 * @returns {Promise} Promesa que se resuelve cuando ambas listas están cargadas
 */
function cargarDatosAuxiliares() {
    const promesas = [];

    // Cargar usuarios solo si no están ya cargados
    if (listaUsuarios.length === 0) {
        promesas.push(
            fetch('CFrontal.php?controlador=Pedidos&metodo=getUsuariosJSON')
                .then(r => r.json())
                .then(data => { listaUsuarios = data; })
        );
    }

    // Cargar productos solo si no están ya cargados
    if (listaProductos.length === 0) {
        promesas.push(
            fetch('CFrontal.php?controlador=Pedidos&metodo=getProductosJSON')
                .then(r => r.json())
                .then(data => { listaProductos = data; })
        );
    }

    return Promise.all(promesas);
}

/**
 * Generar las opciones HTML del select de productos
 * Incluye data-precio para que al seleccionar se autocomplete el precio.
 * 
 * @returns {string} HTML con las opciones del select
 */
function generarOpcionesProductos() {
    let optsProductos = '<option value="">Seleccionar Producto...</option>';
    listaProductos.forEach(p => {
        optsProductos += `<option value="${p.idProducto}" data-precio="${p.precioVenta}">${p.producto} (${p.precioVenta}€)</option>`;
    });
    return optsProductos;
}

// =====================================================================
// BÚSQUEDA Y LISTADO
// =====================================================================

/**
 * Buscar pedidos con filtros y paginación
 * 
 * @param {number} pagina - Número de página (por defecto 1)
 * @param {number} tamPag - Registros por página (por defecto 5)
 */
function buscarPedidos(pagina = 1, tamPag = 5) {
    const params = `pagina=${pagina}&tam_pag=${tamPag}`;
    buscar("Pedidos", "getVistaListadoPedidos", "formularioBuscarPedido", "capaResultadosPedidos", params);
}

/**
 * Mostrar todos los pedidos (sin filtros)
 */
function verTodosPedidos() {
    document.getElementById("formularioBuscarPedido").reset();
    buscar("Pedidos", "getVistaListadoPedidos", "formularioBuscarPedido", "capaResultadosPedidos");
}

// =====================================================================
// FORMULARIO DE PEDIDO (CREAR / EDITAR)
// =====================================================================

/**
 * Preparar y mostrar el formulario para CREAR un nuevo pedido
 * Precarga los datos auxiliares (usuarios, productos) y luego renderiza.
 */
function mostrarFormularioPedido() {
    lineasPedido = [];  // Empezar sin líneas
    cargarDatosAuxiliares().then(() => {
        renderFormularioPedido();
    });
}

/**
 * Renderizar el formulario de pedido (creación o edición)
 * Si recibe datos de un pedido existente, los usa para rellenar los campos.
 * 
 * @param {Object|null} pedido - Datos del pedido (null = creación nueva)
 */
function renderFormularioPedido(pedido = null) {
    const esEdicion = pedido !== null;
    const titulo = esEdicion ? 'Editar Pedido #' + pedido.idPedido : 'Nuevo Pedido';

    // Generar opciones del select de usuarios
    let optsUsuarios = '<option value="">Seleccionar Usuario...</option>';
    listaUsuarios.forEach(u => {
        const selected = (esEdicion && pedido.idUsuario == u.idUsuario) ? 'selected' : '';
        optsUsuarios += `<option value="${u.idUsuario}" ${selected}>${u.nombre} ${u.apellido1}</option>`;
    });

    // Generar la tabla de líneas de detalle existentes
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

    // Construir el HTML completo del formulario
    const formulario = `
        <!-- Overlay oscuro de fondo -->
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;"></div>
        
        <!-- Modal del formulario de pedido -->
        <div class="p-4 rounded shadow" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60%; min-width: 500px; max-height: 90vh; overflow-y: auto; z-index: 1050; background-color: var(--surface-color); color: var(--bs-body-color); border: 1px solid var(--bs-border-color);">
            <h4 class="mb-4" style="color: var(--bs-light) !important;">${titulo}</h4>
            <div id="mensajesPedido"></div>
            
            <form id="formPedido">
                ${esEdicion ? `<input type="hidden" id="idPedidoEdit" value="${pedido.idPedido}">` : ''}
                
                <!-- Cabecera del pedido: usuario y fecha -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-white">Usuario *</label>
                        <select class="form-control" id="pedidoUsuario" required>${optsUsuarios}</select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white">Fecha *</label>
                        <input type="date" class="form-control" id="pedidoFecha" value="${esEdicion ? pedido.fecha : new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white">Estado</label>
                        <select class="form-control" id="pedidoEstado">
                            <option value="P" ${(!esEdicion || pedido.estado === 'P') ? 'selected' : ''}>Pendiente</option>
                            <option value="C" ${(esEdicion && pedido.estado === 'C') ? 'selected' : ''}>Completado</option>
                        </select>
                    </div>
                </div>

                <!-- Sección para añadir líneas de detalle -->
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

                <!-- Tabla con las líneas de detalle actuales -->
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

                <!-- Botones de acción -->
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
}

// =====================================================================
// GESTIÓN DE LÍNEAS DE DETALLE
// =====================================================================

/**
 * Añadir una nueva línea de detalle al pedido
 * Recoge los datos de los inputs (producto, cantidad, precio) y los añade al array.
 */
function addLinea() {
    const select = document.getElementById('lineaProducto');
    const idProducto = select.value;
    const producto = select.options[select.selectedIndex].text;
    const cantidad = parseInt(document.getElementById('lineaCantidad').value);
    const precio = parseFloat(document.getElementById('lineaPrecio').value);

    // Validar que todos los campos estén completos
    if (!idProducto || !cantidad || !precio) {
        alert("Complete todos los campos de la línea");
        return;
    }

    // Añadir la línea al array
    lineasPedido.push({
        idProducto: idProducto,
        producto: producto,
        cantidad: cantidad,
        precioUnitario: precio
    });

    // Refrescar el formulario para que se vea la nueva línea en la tabla
    const pedidoActual = document.getElementById('idPedidoEdit');
    if (pedidoActual) {
        // Si estamos editando, re-renderizar con los datos del pedido
        renderFormularioPedido({
            idPedido: pedidoActual.value,
            idUsuario: document.getElementById('pedidoUsuario').value,
            fecha: document.getElementById('pedidoFecha').value,
            estado: document.getElementById('pedidoEstado').value
        });
    } else {
        // Si estamos creando, re-renderizar vacío (manteniendo las líneas en el array)
        renderFormularioPedido();
    }
}

/**
 * Eliminar una línea de detalle del pedido por su índice
 * 
 * @param {number} index - Índice de la línea a eliminar en el array lineasPedido
 */
function removeLinea(index) {
    lineasPedido.splice(index, 1);

    // Refrescar el formulario
    const pedidoActual = document.getElementById('idPedidoEdit');
    if (pedidoActual) {
        renderFormularioPedido({
            idPedido: pedidoActual.value,
            idUsuario: document.getElementById('pedidoUsuario').value,
            fecha: document.getElementById('pedidoFecha').value,
            estado: document.getElementById('pedidoEstado').value
        });
    } else {
        renderFormularioPedido();
    }
}

// =====================================================================
// GUARDAR / ACTUALIZAR / ELIMINAR
// =====================================================================

/**
 * Validar datos del formulario y enviar petición para crear o actualizar un pedido
 * Detecta automáticamente si es creación o edición según si existe idPedidoEdit.
 */
function guardarPedido() {
    const idUsuario = document.getElementById('pedidoUsuario').value;
    const fecha = document.getElementById('pedidoFecha').value;
    const estado = document.getElementById('pedidoEstado').value;
    const idPedidoEdit = document.getElementById('idPedidoEdit');

    // Validar campos obligatorios
    if (!idUsuario || !fecha) {
        alert("Seleccione un usuario y una fecha");
        return;
    }

    // Validar que haya al menos una línea de detalle
    if (lineasPedido.length === 0) {
        alert("Debe añadir al menos un producto al pedido");
        return;
    }

    // Preparar datos para enviar al servidor
    const datos = new URLSearchParams({
        controlador: 'Pedidos',
        metodo: idPedidoEdit ? 'actualizarPedido' : 'crearPedido',
        idUsuario: idUsuario,
        fecha: fecha,
        estado: estado,
        detalles: JSON.stringify(lineasPedido)  // Líneas como JSON
    });

    // Si es edición, añadir el ID del pedido
    if (idPedidoEdit) {
        datos.append('idPedido', idPedidoEdit.value);
    }

    // Enviar al servidor por POST
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

/**
 * Cargar datos de un pedido existente y mostrar el formulario de edición
 * Obtiene la cabecera y las líneas del servidor.
 * 
 * @param {number} idPedido - ID del pedido a editar
 */
function editarPedido(idPedido) {
    cargarDatosAuxiliares().then(() => {
        fetch(`CFrontal.php?controlador=Pedidos&metodo=obtenerPedido&idPedido=${idPedido}`)
        .then(r => r.json())
        .then(pedido => {
            if (pedido.error) {
                alert(pedido.error);
            } else {
                // Cargar las líneas del pedido en el array global
                lineasPedido = pedido.detalles.map(d => ({
                    idProducto: d.idProducto,
                    producto: d.producto,
                    cantidad: parseInt(d.cantidad),
                    precioUnitario: parseFloat(d.precioUnitario)
                }));
                // Mostrar el formulario con los datos del pedido
                renderFormularioPedido(pedido);
            }
        });
    });
}

/**
 * Eliminar un pedido (baja lógica) previa confirmación
 * 
 * @param {number} idPedido - ID del pedido a eliminar
 */
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

/**
 * Cerrar el formulario modal de pedido y limpiar datos
 */
function cancelarFormularioPedido() {
    document.getElementById("capaPedidoFormulario").innerHTML = "";
    document.getElementById("capaPedidoFormulario").style.display = "none";
    lineasPedido = [];  // Limpiar las líneas en memoria
}
