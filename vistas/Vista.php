<?php
/**
 * Vista.php - Clase base para renderizar vistas
 * 
 * Proporciona el método estático render() que incluye un archivo de vista PHP
 * y le pasa datos mediante extract(), haciendo que las claves del array
 * estén disponibles como variables dentro de la vista.
 * 
 * Ejemplo de uso:
 *   Vista::render('vistas/Usuarios/VUsuariosPrincipal.php');
 *   Vista::render('vistas/VPaginacion.php', ['totalRegistros' => 50, 'pagActual' => 1]);
 */
class Vista {

    /**
     * Renderiza una vista PHP pasándole datos opcionales
     * 
     * @param string $rutaVista  Ruta relativa del archivo de vista a incluir
     * @param array  $datos      Array asociativo con datos para la vista.
     *                           Cada clave se convierte en una variable accesible en la vista.
     */
    static public function render($rutaVista, $datos = array()) {
        // extract() convierte las claves del array en variables locales
        // Ej: ['nombre' => 'Juan'] crea la variable $nombre = 'Juan'
        extract($datos);
        // Incluir el archivo de vista (que puede usar las variables extraídas)
        require($rutaVista);
    }
}
?>