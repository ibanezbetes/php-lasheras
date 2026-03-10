<?php
/**
 * Controlador.php - Clase base para todos los controladores
 * 
 * Todos los controladores de la aplicación (CUsuarios, CProductos, CPedidos, CMenus)
 * heredan de esta clase. Aquí se pueden añadir funciones comunes que usen
 * todos los controladores del sistema.
 */
class Controlador {
    
    /**
     * Constructor base
     * Se puede extender en las clases hijas con parent::__construct()
     */
    function __construct() {}
}
?>
