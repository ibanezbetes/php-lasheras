<!-- ============================================================
     VProductosPrincipal.php - Vista principal del módulo de Productos
     
     Se carga por AJAX en #capaContenido cuando el usuario selecciona
     "Productos" en el menú de navegación.
     
     Contiene:
     - Formulario de búsqueda por nombre de producto
     - Botones: Buscar, Ver Todos, Nuevo
     - Área de resultados (tabla de productos + paginación)
     - Panel lateral para crear/editar producto
     
     Funciones JS relacionadas: buscarProductos(), verTodosProductos(),
     mostrarFormularioCrearProducto() (en productos.js)
     ============================================================ -->

<?php
echo '
<div class="container-fluid">
    <!-- Formulario para buscar productos -->
    <form id="formularioBuscarProducto">
        <div class="row">
            <div class="col-md-8 col-sm-12">
                <label for="producto" class="form-label">Buscar por Producto:</label>
                <input type="text" class="form-control" id="producto" name="producto" placeholder="Buscar producto...">
            </div>
            <div class="col-md-4 col-sm-12 d-flex align-items-end">
                <button type="button" class="btn btn-primary me-2" onclick="buscarProductos();">🔍 Buscar</button>
                <button type="button" class="btn btn-success me-2" onclick="verTodosProductos();">📦 Ver Todos</button>
                <button type="button" class="btn btn-secondary" onclick="mostrarFormularioCrearProducto();">➕ Nuevo</button>
            </div>
        </div>
    </form>

    <div class="row mt-4">
        <!-- Columna izquierda: tabla de resultados -->
        <div class="col-md-8">
            <div id="capaResultadosProductos">
                <p class="text-muted text-center">Utilice los campos de búsqueda para encontrar productos</p>
            </div>
        </div>

        <!-- Columna derecha: panel lateral de creación/edición -->
        <div class="col-md-4">
            <div id="capaEditarCrearProducto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Gestión de Productos</h5>
                    </div>
                    <div class="card-body">
                        <!-- El formulario se genera dinámicamente desde productos.js -->
                        <div id="formularioProducto" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';
?>