/**
 * js/productos.js - Lógica del módulo de Productos (lado cliente)
 * 
 * Gestiona las operaciones CRUD de productos desde el navegador:
 * - Buscar y listar productos (con paginación)
 * - Mostrar formulario de creación
 * - Mostrar formulario de edición
 * - Validar datos antes de enviar
 * - Guardar/actualizar/eliminar productos via AJAX
 * 
 * Depende de: utils.js (buscar, mostrarError, mostrarExito, limpiarMensajes)
 */

// =====================================================================
// BÚSQUEDA Y LISTADO
// =====================================================================

/**
 * Buscar productos con filtros y paginación
 * 
 * @param {number} pagina - Número de página (por defecto 1)
 * @param {number} tamPag - Registros por página (por defecto 5)
 */
function buscarProductos(pagina = 1, tamPag = 5) {
  const params = `pagina=${pagina}&tam_pag=${tamPag}`;
  buscar("Productos", "getVistaListadoProductos", "formularioBuscarProducto", "capaResultadosProductos", params);
}

/**
 * Mostrar todos los productos (sin filtros)
 */
function verTodosProductos() {
  document.getElementById("formularioBuscarProducto").reset();
  buscar("Productos", "getVistaListadoProductos", "formularioBuscarProducto", "capaResultadosProductos");
}

// =====================================================================
// FORMULARIOS (CREAR / EDITAR)
// =====================================================================

/**
 * Mostrar formulario vacío para crear un nuevo producto
 * Se muestra en el panel lateral derecho de la vista principal.
 */
function mostrarFormularioCrearProducto() {
  const formulario = `
    <div id="mensajesProducto"></div>
    <form id="formProducto">
      <div class="mb-3">
        <label class="form-label">Producto *</label>
        <input type="text" class="form-control" id="productoNombre" name="producto" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" id="productoDescripcion" name="descripcion" rows="3"></textarea>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">ID Categoría</label>
          <input type="number" class="form-control" id="productoCategoria" name="idCategoria" value="1">
        </div>
        <div class="col-md-6">
          <label class="form-label">Stock Actual</label>
          <input type="number" class="form-control" id="productoStock" name="stock" value="0">
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Stock Mínimo</label>
          <input type="number" class="form-control" id="productoStockMinimo" name="stockMinimo" value="0">
        </div>
        <div class="col-md-6">
          <label class="form-label">Precio Compra</label>
          <input type="number" step="0.01" class="form-control" id="productoPrecioCompra" name="precioCompra" value="0">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Precio Venta *</label>
        <input type="number" step="0.01" class="form-control" id="productoPrecio" name="precioVenta" required>
      </div>
      </div>
      <div class="d-grid gap-2">
        <button type="button" class="btn btn-primary" onclick="guardarProducto();">Guardar</button>
        <button type="button" class="btn btn-secondary" onclick="cancelarFormularioProducto();">Cancelar</button>
      </div>
    </form>
  `;
  document.getElementById("formularioProducto").innerHTML = formulario;
  document.getElementById("formularioProducto").style.display = "block";
}

/**
 * Cargar datos de un producto del servidor y mostrar el formulario de edición
 * 
 * @param {number} idProducto - ID del producto a editar
 */
function editarProducto(idProducto) {
  fetch(`CFrontal.php?controlador=Productos&metodo=obtenerProducto&idProducto=${idProducto}`)
    .then((response) => response.json())
    .then((producto) => {
      mostrarFormularioEditarProducto(producto);
    });
}

/**
 * Generar y mostrar el formulario de edición con los datos del producto
 * 
 * @param {Object} producto - Datos del producto a editar
 */
function mostrarFormularioEditarProducto(producto) {
  const formulario = `
    <div id="mensajesProducto"></div>
    <form id="formProducto">
      <input type="hidden" id="idProducto" value="${producto.idProducto}">
      <div class="mb-3">
        <label class="form-label">Producto *</label>
        <input type="text" class="form-control" id="productoNombre" value="${producto.producto}" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" id="productoDescripcion" rows="3">${producto.descripcion || ""}</textarea>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">ID Categoría</label>
          <input type="number" class="form-control" id="productoCategoria" value="${producto.idCategoria || 1}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Stock Actual</label>
          <input type="number" class="form-control" id="productoStock" value="${producto.stock || 0}">
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Stock Mínimo</label>
          <input type="number" class="form-control" id="productoStockMinimo" value="${producto.stockMinimo || 0}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Precio Compra</label>
          <input type="number" step="0.01" class="form-control" id="productoPrecioCompra" value="${producto.precioCompra || 0}">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Precio Venta *</label>
        <input type="number" step="0.01" class="form-control" id="productoPrecio" value="${producto.precioVenta}" required>
      </div>
      <div class="d-grid gap-2">
        <button type="button" class="btn btn-primary" onclick="actualizarProducto();">Actualizar</button>
        <button type="button" class="btn btn-secondary" onclick="cancelarFormularioProducto();">Cancelar</button>
      </div>
    </form>
  `;
  document.getElementById("formularioProducto").innerHTML = formulario;
  document.getElementById("formularioProducto").style.display = "block";
}

// =====================================================================
// GUARDAR / ACTUALIZAR / ELIMINAR
// =====================================================================

/**
 * Validar datos y enviar petición para CREAR un nuevo producto
 */
function guardarProducto() {
  limpiarMensajes("mensajesProducto");

  // Recoger valores del formulario
  const producto = document.getElementById("productoNombre").value.trim();
  const precio   = document.getElementById("productoPrecio").value.trim();

  // Validar campos obligatorios
  if (!producto || !precio) {
    mostrarError("mensajesProducto", "El nombre y precio son obligatorios");
    return;
  }

  // Enviar datos al servidor
  let parametros = "controlador=Productos&metodo=crearProducto";
  parametros += "&" + new URLSearchParams(new FormData(document.getElementById("formProducto"))).toString();

  fetch("CFrontal.php?" + parametros)
    .then((response) => response.text())
    .then((data) => {
      if (data.includes("exitosamente")) {
        mostrarExito("mensajesProducto", "Producto creado");
        setTimeout(() => {
          cancelarFormularioProducto();
          verTodosProductos();
        }, 1500);
      } else {
        mostrarError("mensajesProducto", "Error al crear");
      }
    });
}

/**
 * Validar datos y enviar petición para ACTUALIZAR un producto existente
 */
function actualizarProducto() {
  limpiarMensajes("mensajesProducto");

  // Preparar datos para el servidor
  const datos = {
    controlador: "Productos",
    metodo: "actualizarProducto",
    idProducto:  document.getElementById("idProducto").value,
    producto:    document.getElementById("productoNombre").value.trim(),
    descripcion: document.getElementById("productoDescripcion").value.trim(),
    idCategoria: document.getElementById("productoCategoria").value,
    stock:       document.getElementById("productoStock").value,
    stockMinimo: document.getElementById("productoStockMinimo").value,
    precioCompra:document.getElementById("productoPrecioCompra").value,
    precioVenta: document.getElementById("productoPrecio").value
  };

  // Enviar por POST
  fetch("CFrontal.php", { method: "POST", body: new URLSearchParams(datos) })
    .then((response) => response.text())
    .then((data) => {
      if (data.includes("exitosamente")) {
        mostrarExito("mensajesProducto", "Producto actualizado");
        setTimeout(() => {
          cancelarFormularioProducto();
          verTodosProductos();
        }, 1500);
      } else {
        mostrarError("mensajesProducto", "Error al actualizar");
      }
    });
}

/**
 * Cerrar el formulario de producto y limpiar su contenido
 */
function cancelarFormularioProducto() {
  document.getElementById("formularioProducto").style.display = "none";
  document.getElementById("formularioProducto").innerHTML = "";
}

/**
 * Eliminar un producto (baja lógica) previa confirmación
 * 
 * @param {number} idProducto     - ID del producto a eliminar
 * @param {string} nombreProducto - Nombre del producto (para el mensaje)
 */
function eliminarProducto(idProducto, nombreProducto) {
  if (confirm(`¿Eliminar '${nombreProducto}'?`)) {
    const datos = {
      controlador: "Productos",
      metodo: "eliminarProducto",
      idProducto: idProducto
    };

    fetch("CFrontal.php", { method: "POST", body: new URLSearchParams(datos) })
      .then((response) => response.text())
      .then((data) => {
        if (data.includes("exitosamente")) {
          mostrarExito("capaResultadosProductos", "Producto eliminado");
          setTimeout(() => verTodosProductos(), 1500);
        } else {
          mostrarError("capaResultadosProductos", "Error al eliminar");
        }
      });
  }
}
