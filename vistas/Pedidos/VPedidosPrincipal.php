<!-- ============================================================
     VPedidosPrincipal.php - Vista principal del módulo de Pedidos
     
     Se carga por AJAX en #capaContenido cuando el usuario selecciona
     "Pedidos" en el menú de navegación.
     
     Contiene:
     - Formulario de búsqueda (usuario + fecha)
     - Botones: Buscar, Ver Todos, Crear Nuevo Pedido
     - Contenedor para el formulario modal de crear/editar pedido
     - Contenedor para la tabla de resultados con paginación
     
     Funciones JS relacionadas: buscarPedidos(), verTodosPedidos(),
     mostrarFormularioPedido() (en pedidos.js)
     ============================================================ -->

<div class="container-fluid">
        <!-- Formulario de búsqueda de pedidos -->
        <form id="formularioBuscarPedido">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <label for="filtroUsuario" class="form-label">Buscar por Usuario:</label>
                    <input type="text" class="form-control" id="filtroUsuario" name="usuario" placeholder="Nombre de usuario...">
                </div>
                 <div class="col-md-6 col-sm-12">
                    <label for="filtroFecha" class="form-label">Fecha (YYYY-MM-DD):</label>
                    <input type="date" class="form-control" id="filtroFecha" name="fecha">
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="row mt-3">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-primary" onclick="buscarPedidos();">
                        Buscar Pedidos
                    </button>
                    <button type="button" class="btn btn-info ms-2" onclick="verTodosPedidos();">
                        Ver Todos
                    </button>
                    <?php if(isset($permisosPedidos[2])): ?>
                    <button type="button" class="btn btn-success ms-2" onclick="mostrarFormularioPedido();">
                        Crear Nuevo Pedido
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="row mt-4">
            <div class="col-md-12">
                <!-- Contenedor para el formulario modal de crear/editar pedido -->
                <div id="capaPedidoFormulario" style="display:none;" class="mb-4">
                    <!-- El formulario se genera dinámicamente desde pedidos.js -->
                </div>

                <!-- Contenedor donde se mostrará la tabla de resultados -->
                <div id="capaResultadosPedidos">
                    <p class="text-muted text-center">Utilice los campos de búsqueda para encontrar pedidos</p>
                </div>
            </div>
        </div>
</div>
