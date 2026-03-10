<!-- ============================================================
     VUsuariosPrincipal.php - Vista principal del módulo de Usuarios
     
     Se carga por AJAX en #capaContenido cuando el usuario hace clic
     en "Usuarios" en el menú de navegación.
     
     Contiene:
     - Formulario de búsqueda (nombre + email)
     - Botones: Buscar, Ver Todos, Limpiar, Crear Nuevo
     - Contenedor para el formulario modal de crear/editar (inicialmente oculto)
     - Contenedor para la tabla de resultados
     
     Funciones JS relacionadas: buscarUsuarios(), verTodosUsuarios(),
     limpiarBusqueda(), mostrarFormularioCrear() (en usuarios.js)
     ============================================================ -->

<div class="container-fluid">
        <!-- Formulario de búsqueda de usuarios -->
        <form id="formularioBuscar">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <label for="nombre" class="form-label">Buscar por Nombre:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Buscar en campo nombre...">
                </div>
                <div class="col-md-6 col-sm-12">
                    <label for="email" class="form-label">Buscar por Mail:</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Buscar en campo mail...">
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="row mt-3">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-primary" onclick="buscarUsuarios();">
                        Buscar Usuarios
                    </button>
                    <button type="button" class="btn btn-info ms-2" onclick="verTodosUsuarios();">
                        Ver Todos
                    </button>
                    <button type="button" class="btn btn-secondary ms-2" onclick="limpiarBusqueda();">
                        Limpiar
                    </button>
                    <button type="button" class="btn btn-success ms-2" onclick="mostrarFormularioCrear();">
                        Crear Nuevo Usuario
                    </button>
                </div>
            </div>
        </form>

        <div class="row mt-4">
            <div class="col-md-12">
                <!-- Contenedor para el formulario modal de crear/editar (oculto por defecto) -->
                <div id="formularioUsuario" style="display:none;" class="mb-4">
                    <!-- El formulario se genera dinámicamente desde usuarios.js -->
                </div>

                <!-- Contenedor donde se mostrará la tabla de resultados -->
                <div id="capaResultadosBusqueda">
                    <p class="text-muted text-center">Utilice los campos de búsqueda para encontrar usuarios</p>
                </div>
            </div>
        </div>
</div>
