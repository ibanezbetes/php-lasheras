<?php
/**
 * CMenus.php - Controlador del Menú Dinámico
 * 
 * Genera la estructura del menú de navegación a partir de la base de datos.
 * Obtiene los menús de nivel 1 (principales) y sus submenús (nivel 2),
 * los organiza en una estructura jerárquica y los pasa a la vista
 * VMenuDinamico.php para que los renderice.
 * 
 * Ventaja: permite añadir o modificar opciones del menú directamente
 * en la tabla 'menus' de la BD, sin necesidad de tocar el código.
 */
require_once 'controladores/Controlador.php';
require_once 'vistas/Vista.php';
require_once 'modelos/MMenus.php';

class CMenus extends Controlador {
    /** @var MMenus Modelo de acceso a la tabla menus */
    private $modelo;

    /**
     * Constructor - Inicializa el modelo de menús
     */
    public function __construct() {
        $this->modelo = new MMenus();
    }

    /**
     * Obtener y renderizar el menú dinámico completo
     * 
     * Proceso:
     * 1. Obtiene los menús de nivel 1 (los que aparecen en la barra horizontal)
     * 2. Para cada menú nivel 1, busca si tiene submenús (nivel 2 / desplegables)
     * 3. Genera un array estructurado con formato:
     *    [
     *      ['item' => datos_menu_nivel1, 'submenus' => [datos_submenu1, datos_submenu2, ...]],
     *      ['item' => datos_menu_nivel1, 'submenus' => []],  // sin submenús
     *    ]
     * 4. Pasa esta estructura a la vista para que la pinte
     * 
     * @param array $datos Parámetros recibidos (no se usan)
     */
    public function getMenuDinamico($datos = array()) {
        // Obtener los menús de nivel 1 (padres, aparecen en la barra principal)
        $menusNivel1 = $this->modelo->obtenerMenusNivel1();

        // Construir la estructura jerárquica
        $menuEstructurado = array();
        foreach ($menusNivel1 as $menu) {
            // Para cada menú nivel 1, buscar sus submenús (nivel 2)
            $submenus = $this->modelo->obtenerSubmenus($menu['idOpcion']);

            $menuEstructurado[] = array(
                'item'     => $menu,       // Datos del menú padre
                'submenus' => $submenus    // Array de submenús (puede estar vacío)
            );
        }

        // Renderizar la vista pasándole la estructura del menú
        Vista::render('vistas/VMenuDinamico.php', array(
            'menuEstructurado' => $menuEstructurado
        ));
    }
}
?>
