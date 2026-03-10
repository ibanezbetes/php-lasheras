/**
 * js/usuarios.js - Lógica del módulo de Usuarios (lado cliente)
 * 
 * Gestiona las operaciones CRUD de usuarios desde el navegador:
 * - Buscar y listar usuarios (con paginación)
 * - Mostrar formulario de creación (modal)
 * - Mostrar formulario de edición (modal)
 * - Validar datos antes de enviar
 * - Guardar/actualizar/eliminar usuarios via AJAX
 * 
 * Depende de: utils.js (buscar, mostrarError, mostrarExito, validarEmail, validarMovil)
 */

// =====================================================================
// BÚSQUEDA Y LISTADO
// =====================================================================

/**
 * Buscar usuarios con filtros y paginación
 * Llama a la función genérica buscar() de utils.js
 * 
 * @param {number} pagina - Número de página (por defecto 1)
 * @param {number} tamPag - Registros por página (por defecto 5)
 */
function buscarUsuarios(pagina = 1, tamPag = 5) {
  const params = `pagina=${pagina}&tam_pag=${tamPag}`;
  buscar("Usuarios", "getVistaListadoUsuarios", "formularioBuscar", "capaResultadosBusqueda", params);
}

/**
 * Mostrar todos los usuarios (sin filtros)
 * Resetea el formulario de búsqueda y carga todos los resultados.
 */
function verTodosUsuarios() {
  document.getElementById("formularioBuscar").reset();
  buscar("Usuarios", "getVistaListadoUsuarios", "formularioBuscar", "capaResultadosBusqueda");
}

/**
 * Limpiar el formulario de búsqueda y los resultados
 */
function limpiarBusqueda() {
  document.getElementById("formularioBuscar").reset();
  document.getElementById("capaResultadosBusqueda").innerHTML = '<p class="text-muted text-center">Utilice los campos de búsqueda</p>';
}

// =====================================================================
// FORMULARIOS (CREAR / EDITAR)
// =====================================================================

/**
 * Mostrar el formulario modal para crear un nuevo usuario
 * Genera el HTML del formulario y lo muestra como modal con overlay.
 */
function mostrarFormularioCrear() {
  const formulario = `
    <!-- Overlay oscuro de fondo -->
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;"></div>
    
    <!-- Formulario modal centrado -->
    <div class="p-4 rounded shadow" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 33%; min-width: 400px; max-height: 90vh; overflow-y: auto; z-index: 1050; background-color: var(--surface-color); color: var(--bs-body-color); border: 1px solid var(--bs-border-color);">
        <h4 class="mb-4" style="color: var(--bs-light) !important;">Crear Nuevo Usuario</h4>
        <div id="mensajesUsuario"></div>
        <form id="formUsuario">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre *</label>
              <input type="text" class="form-control" id="nombreUsuario" name="nombre" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Primer Apellido *</label>
              <input type="text" class="form-control" id="apellido1Usuario" name="apellido1" required>
            </div>
          </div>
          
          <div class="row">
             <div class="col-md-6 mb-3">
              <label class="form-label">Segundo Apellido</label>
              <input type="text" class="form-control" id="apellido2Usuario" name="apellido2">
            </div>
             <div class="col-md-6 mb-3">
              <label class="form-label">Email *</label>
              <input type="email" class="form-control" id="mailUsuario" name="mail" required>
            </div>
          </div>
          
          <div class="row">
             <div class="col-md-6 mb-3">
              <label class="form-label">Móvil</label>
              <input type="text" class="form-control" id="movilUsuario" name="movil">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Sexo</label>
              <select class="form-control" id="sexoUsuario" name="sexo">
                <option value="H">Hombre</option>
                <option value="M">Mujer</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Login *</label>
              <input type="text" class="form-control" id="loginUsuario" name="login" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Contraseña *</label>
              <input type="password" class="form-control" id="passUsuario" name="pass" required>
            </div>
          </div>
          
          <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
            <button type="button" class="btn btn-primary" onclick="guardarUsuario();">Guardar</button>
            <button type="button" class="btn btn-secondary" onclick="cancelarFormulario();">Cancelar</button>
          </div>
        </form>
    </div>
  `;

  document.getElementById("formularioUsuario").innerHTML = formulario;
  document.getElementById("formularioUsuario").style.display = "block";
}

/**
 * Cargar datos de un usuario del servidor y mostrar el formulario de edición
 * 
 * @param {number} idUsuario - ID del usuario a editar
 */
function editarUsuario(idUsuario) {
  fetch(`CFrontal.php?controlador=Usuarios&metodo=obtenerUsuario&idUsuario=${idUsuario}`)
    .then((response) => response.json())
    .then((usuario) => {
      if (usuario.error) {
        alert("Error al cargar usuario");
      } else {
        mostrarFormularioEditar(usuario);
      }
    });
}

/**
 * Generar y mostrar el formulario modal de edición con los datos del usuario
 * 
 * @param {Object} usuario - Datos del usuario a editar
 */
function mostrarFormularioEditar(usuario) {
  const formulario = `
    <!-- Overlay oscuro de fondo -->
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;"></div>
    
    <!-- Formulario modal centrado -->
    <div class="p-4 rounded shadow" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 33%; min-width: 400px; max-height: 90vh; overflow-y: auto; z-index: 1050; background-color: var(--surface-color); color: var(--bs-body-color); border: 1px solid var(--bs-border-color);">
        <h4 class="mb-4" style="color: var(--bs-light) !important;">Editar Usuario</h4>
        <div id="mensajesUsuario"></div>
        <form id="formUsuario">
          <input type="hidden" id="idUsuario" value="${usuario.idUsuario}">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre *</label>
              <input type="text" class="form-control" id="nombreUsuario" value="${usuario.nombre}" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Primer Apellido *</label>
              <input type="text" class="form-control" id="apellido1Usuario" value="${usuario.apellido1}" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Segundo Apellido</label>
              <input type="text" class="form-control" id="apellido2Usuario" value="${usuario.apellido2 || ""}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email *</label>
              <input type="email" class="form-control" id="mailUsuario" value="${usuario.mail}" required>
            </div>
          </div>
    
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Móvil</label>
              <input type="text" class="form-control" id="movilUsuario" value="${usuario.movil || ""}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Login *</label>
              <input type="text" class="form-control" id="loginUsuario" value="${usuario.login}" required>
            </div>
          </div>
            
          <div class="row">
             <div class="col-md-12 mb-3">
              <label class="form-label">Sexo</label>
              <select class="form-control" id="sexoUsuario">
                <option value="H" ${usuario.sexo === "H" ? "selected" : ""}>Hombre</option>
                <option value="M" ${usuario.sexo === "M" ? "selected" : ""}>Mujer</option>
              </select>
            </div>
          </div>
    
          <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
            <button type="button" class="btn btn-primary" onclick="actualizarUsuario();">Actualizar</button>
            <button type="button" class="btn btn-secondary" onclick="cancelarFormulario();">Cancelar</button>
          </div>
        </form>
    </div>
  `;

  document.getElementById("formularioUsuario").innerHTML = formulario;
  document.getElementById("formularioUsuario").style.display = "block";
}

// =====================================================================
// GUARDAR / ACTUALIZAR / ELIMINAR
// =====================================================================

/**
 * Validar datos del formulario y enviar petición para CREAR un nuevo usuario
 * Valida campos obligatorios, formato de email y móvil antes de enviar.
 */
function guardarUsuario() {
  limpiarMensajes("mensajesUsuario");

  // Recoger valores de los campos del formulario
  const nombre    = document.getElementById("nombreUsuario").value.trim();
  const apellido1 = document.getElementById("apellido1Usuario").value.trim();
  const mail      = document.getElementById("mailUsuario").value.trim();
  const movil     = document.getElementById("movilUsuario").value.trim();
  const login     = document.getElementById("loginUsuario").value.trim();
  const pass      = document.getElementById("passUsuario").value.trim();

  // Validar campos obligatorios (marcados con *)
  if (!nombre || !apellido1 || !mail || !login || !pass) {
    mostrarError("mensajesUsuario", "Todos los campos marcados con * son obligatorios");
    return;
  }

  // Validar formato de email
  if (!validarEmail(mail)) {
    mostrarError("mensajesUsuario", "El email no es válido");
    return;
  }

  // Validar formato de móvil (solo si se ha rellenado)
  if (movil && !validarMovil(movil)) {
    mostrarError("mensajesUsuario", "El móvil no es válido");
    return;
  }

  // Enviar datos al servidor por GET
  let parametros = "controlador=Usuarios&metodo=crearUsuario";
  parametros += "&" + new URLSearchParams(new FormData(document.getElementById("formUsuario"))).toString();

  fetch("CFrontal.php?" + parametros)
    .then((response) => response.text())
    .then((data) => {
      if (data.includes("exitosamente")) {
        mostrarExito("mensajesUsuario", "Usuario creado correctamente");
        // Cerrar formulario y refrescar listado tras 1.5 segundos
        setTimeout(() => {
          cancelarFormulario();
          verTodosUsuarios();
        }, 1500);
      } else {
        mostrarError("mensajesUsuario", "Error al crear el usuario");
      }
    });
}

/**
 * Validar datos y enviar petición para ACTUALIZAR un usuario existente
 */
function actualizarUsuario() {
  limpiarMensajes("mensajesUsuario");

  // Recoger valores del formulario
  const nombre    = document.getElementById("nombreUsuario").value.trim();
  const apellido1 = document.getElementById("apellido1Usuario").value.trim();
  const apellido2 = document.getElementById("apellido2Usuario").value.trim();
  const mail      = document.getElementById("mailUsuario").value.trim();
  const movil     = document.getElementById("movilUsuario").value.trim();
  const login     = document.getElementById("loginUsuario").value.trim();
  const sexo      = document.getElementById("sexoUsuario").value;

  // Validar campos obligatorios
  if (!nombre || !apellido1 || !mail || !login) {
    mostrarError("mensajesUsuario", "Todos los campos obligatorios deben estar completos");
    return;
  }

  // Validar email
  if (!validarEmail(mail)) {
    mostrarError("mensajesUsuario", "El email no es válido");
    return;
  }

  // Validar móvil (si tiene valor)
  if (movil && !validarMovil(movil)) {
    mostrarError("mensajesUsuario", "El móvil no es válido");
    return;
  }

  // Enviar datos al servidor por POST
  const datos = {
    controlador: "Usuarios",
    metodo: "actualizarUsuario",
    idUsuario: document.getElementById("idUsuario").value,
    nombre: nombre,
    apellido1: apellido1,
    apellido2: apellido2,
    mail: mail,
    movil: movil,
    login: login,
    sexo: sexo
  };

  fetch("CFrontal.php", {
    method: "POST",
    body: new URLSearchParams(datos)
  })
    .then((response) => response.text())
    .then((data) => {
      if (data.includes("exitosamente")) {
        mostrarExito("mensajesUsuario", "Usuario actualizado correctamente");
        setTimeout(() => {
          cancelarFormulario();
          verTodosUsuarios();
        }, 1500);
      } else {
        mostrarError("mensajesUsuario", "Error al actualizar");
      }
    });
}

/**
 * Cerrar el formulario modal (crear o editar)
 * Oculta el contenedor y limpia su contenido HTML.
 */
function cancelarFormulario() {
  document.getElementById("formularioUsuario").style.display = "none";
  document.getElementById("formularioUsuario").innerHTML = "";
}

/**
 * Eliminar un usuario (baja lógica) previa confirmación
 * Muestra un diálogo de confirmación antes de eliminar.
 * 
 * @param {number} idUsuario     - ID del usuario a eliminar
 * @param {string} nombreUsuario - Nombre del usuario (para el mensaje de confirmación)
 */
function eliminarUsuario(idUsuario, nombreUsuario) {
  if (confirm(`¿Eliminar al usuario '${nombreUsuario}'?`)) {
    const datos = {
      controlador: "Usuarios",
      metodo: "eliminarUsuario",
      idUsuario: idUsuario
    };

    fetch("CFrontal.php", {
      method: "POST",
      body: new URLSearchParams(datos)
    })
      .then((response) => response.text())
      .then((data) => {
        if (data.includes("exitosamente")) {
          mostrarExito("capaResultadosBusqueda", "Usuario eliminado");
          setTimeout(() => verTodosUsuarios(), 1500);
        } else {
          mostrarError("capaResultadosBusqueda", "Error al eliminar");
        }
      });
  }
}
