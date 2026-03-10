/**
 * js/utils.js - Funciones de utilidad compartidas
 * 
 * Contiene funciones auxiliares que se usan en todos los módulos:
 * - Validations de formularios (email, móvil)
 * - Mensajes de éxito/error (alertas Bootstrap)
 * - Carga dinámica de vistas por AJAX (obtenerVista)
 * - Envío de formularios de búsqueda por AJAX (buscar)
 */

// =====================================================================
// VALIDACIONES
// =====================================================================

/**
 * Validar formato de email
 * @param {string} email - Dirección de email a validar
 * @returns {boolean} true si el formato es válido (usuario@dominio.ext)
 */
function validarEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

/**
 * Validar formato de teléfono móvil español
 * Debe tener 9 dígitos y empezar por 6, 7, 8 o 9
 * @param {string} movil - Número de teléfono a validar
 * @returns {boolean} true si el formato es válido
 */
function validarMovil(movil) {
  const regex = /^[6-9]\d{8}$/;
  return regex.test(movil.replace(/\s/g, ''));
}

// =====================================================================
// MENSAJES (alertas Bootstrap)
// =====================================================================

/**
 * Mostrar un mensaje de error en un contenedor HTML
 * @param {string} contenedorId - ID del elemento HTML donde mostrar el error
 * @param {string} mensaje - Texto del error a mostrar
 */
function mostrarError(contenedorId, mensaje) {
  const contenedor = document.getElementById(contenedorId);
  if (contenedor) {
    contenedor.innerHTML = `
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
  }
}

/**
 * Mostrar un mensaje de éxito en un contenedor HTML
 * @param {string} contenedorId - ID del elemento HTML donde mostrar el éxito
 * @param {string} mensaje - Texto del mensaje de éxito
 */
function mostrarExito(contenedorId, mensaje) {
  const contenedor = document.getElementById(contenedorId);
  if (contenedor) {
    contenedor.innerHTML = `
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Éxito:</strong> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
  }
}

/**
 * Limpiar todos los mensajes de un contenedor
 * @param {string} contenedorId - ID del elemento HTML a limpiar
 */
function limpiarMensajes(contenedorId) {
  const contenedor = document.getElementById(contenedorId);
  if (contenedor) {
    contenedor.innerHTML = '';
  }
}

// =====================================================================
// CARGA DINÁMICA DE VISTAS (AJAX)
// =====================================================================

/**
 * Cargar una vista del servidor y mostrarla en un contenedor HTML
 * Esta función es la base del sistema de navegación SPA (Single Page Application).
 * Se llama desde los menús de navegación onclick="obtenerVista(...)".
 * 
 * @param {string} controlador     - Nombre del controlador (ej: 'Usuarios', 'Productos', 'Pedidos')
 * @param {string} metodo          - Nombre del método a ejecutar en el controlador
 * @param {string} destino         - ID del elemento HTML donde se pintará la respuesta
 * @param {string} parametrosExtra - Parámetros adicionales para la URL (opcional)
 * 
 * Ejemplo de uso (desde el menú):
 *   obtenerVista('Usuarios', 'getVistaUsuariosPrincipal', 'capaContenido');
 */
function obtenerVista(controlador, metodo, destino, parametrosExtra = "") {
  let parametros = "controlador=" + controlador + "&metodo=" + metodo;
  if (parametrosExtra) {
    parametros += "&" + parametrosExtra;
  }

  // Petición AJAX al Controlador Frontal
  fetch("CFrontal.php?" + parametros)
    .then((res) => res.text())
    .then((respuesta) => {
      // Insertar la vista devuelta en el contenedor destino
      document.getElementById(destino).innerHTML = respuesta;
    })
    .catch(() => {
      document.getElementById(destino).innerHTML = "Error al cargar la vista";
    });
}

/**
 * Enviar un formulario de búsqueda al servidor por AJAX
 * Recoge automáticamente todos los campos del formulario y los envía.
 * La respuesta (que suele ser una tabla HTML) se muestra en el contenedor destino.
 * 
 * @param {string} controlador     - Nombre del controlador
 * @param {string} metodo          - Nombre del método de búsqueda
 * @param {string} formulario      - ID del formulario cuyos datos se enviarán
 * @param {string} destino         - ID del contenedor donde mostrar los resultados
 * @param {string} parametrosExtra - Parámetros adicionales (ej: 'pagina=2&tam_pag=10')
 * 
 * Ejemplo de uso:
 *   buscar('Usuarios', 'getVistaListadoUsuarios', 'formularioBuscar', 'capaResultados');
 */
function buscar(controlador, metodo, formulario, destino, parametrosExtra = "") {
  let parametros = "controlador=" + controlador + "&metodo=" + metodo;
  if (parametrosExtra) {
    parametros += "&" + parametrosExtra;
  }

  // Serializar los datos del formulario y añadirlos a los parámetros
  parametros += "&" + new URLSearchParams(new FormData(document.getElementById(formulario))).toString();

  // Petición AJAX
  fetch("CFrontal.php?" + parametros)
    .then((res) => res.text())
    .then((vista) => {
      document.getElementById(destino).innerHTML = vista;
    })
    .catch(() => {
      document.getElementById(destino).innerHTML = "Error al buscar";
    });
}
