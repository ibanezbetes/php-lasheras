/**
 * js/paginacion.js - Funciones de soporte para la paginación
 * 
 * Estas funciones complementan al componente VPaginacion.php.
 * Se usan cuando la paginación se controla desde campos ocultos del formulario
 * (patrón alternativo al callback directo que usa VPaginacion.php).
 * 
 * NOTA: El componente VPaginacion.php ya llama directamente a las funciones
 * callback (buscarUsuarios, buscarProductos, buscarPedidos) con los parámetros
 * de página y tamaño. Estas funciones son un mecanismo auxiliar.
 */

/**
 * Cambiar a una página concreta
 * Actualiza el campo oculto 'pagina' y relanza la búsqueda.
 * 
 * @param {number} page - Número de página al que se quiere navegar
 */
function cambiarPagina(page) {
    // Actualizar el campo oculto del formulario con la página solicitada
    document.getElementById('pagina').value = page;

    // Llamar a la función de búsqueda del módulo activo
    if (typeof buscarUsuarios === 'function') {
        buscarUsuarios();
    }
    if (typeof buscarProductos === 'function') {
        buscarProductos();
    }
    if (typeof buscarPedidos === 'function') {
        buscarPedidos();
    }
}

/**
 * Cambiar el número de resultados por página
 * Actualiza el tamaño de página y vuelve a la página 1.
 * 
 * @param {number} size - Nuevo número de resultados por página
 */
function cambiarTamPag(size) {
    // Actualizar el tamaño de página
    document.getElementById('tam_pag').value = size;

    // Volver a la página 1 para evitar offsets fuera de rango
    document.getElementById('pagina').value = 1;

    // Relanzar búsqueda
    if (typeof buscarUsuarios === 'function') {
        buscarUsuarios();
    }
    if (typeof buscarProductos === 'function') {
        buscarProductos();
    }
    if (typeof buscarPedidos === 'function') {
        buscarPedidos();
    }
}
