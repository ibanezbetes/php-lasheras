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

        // Obtener las opciones permitidas del usuario desde la sesión
        $opcionesPermitidas = isset($_SESSION['opcionesPermitidas']) ? $_SESSION['opcionesPermitidas'] : array();

        // Construir la estructura jerárquica
        $menuEstructurado = array();
        foreach ($menusNivel1 as $menu) {
            // Verificar si el menú nivel 1 tiene permisos o es público
            $submenus = $this->modelo->obtenerSubmenus($menu['idOpcion']);

            // Filtrar submenús según permisos del usuario
            $submenusFiltrados = array();
            foreach ($submenus as $submenu) {
                // Mostrar si el usuario tiene permiso o si la opción es pública
                if (in_array($submenu['idOpcion'], $opcionesPermitidas)) {
                    $submenusFiltrados[] = $submenu;
                }
            }

            // Si el menú nivel 1 tiene submenús, solo mostrarlo si al menos uno está permitido
            // Si no tiene submenús (es opción directa), verificar permiso individual
            if (!empty($submenus)) {
                // Es un desplegable: mostrar solo si tiene al menos un submenú permitido
                if (!empty($submenusFiltrados)) {
                    $menuEstructurado[] = array(
                        'item'     => $menu,
                        'submenus' => $submenusFiltrados
                    );
                }
            } else {
                // Es opción directa: verificar si tiene permiso
                if (in_array($menu['idOpcion'], $opcionesPermitidas)) {
                    $menuEstructurado[] = array(
                        'item'     => $menu,
                        'submenus' => array()
                    );
                }
            }
        }

        // Renderizar la vista pasándole la estructura del menú
        Vista::render('vistas/VMenuDinamico.php', array(
            'menuEstructurado' => $menuEstructurado
        ));
    }
}
?>
