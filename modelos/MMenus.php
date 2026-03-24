<?php
/**
 * MMenus.php - Modelo del Menú Dinámico
 * 
 * Accede a la tabla 'menus' de la base de datos para obtener las opciones
 * del menú de navegación. La tabla almacena tanto los menús de nivel 1
 * (barra principal) como los submenús (nivel 2, desplegables).
 * 
 * Estructura de la tabla 'menus':
 *   - idOpcion:  ID único de la opción (auto_increment)
 *   - etiqueta:  Texto que se muestra en el menú
 *   - idPadre:   NULL = menú nivel 1 | ID del padre = submenú nivel 2
 *   - posicion:  Orden de aparición (número)
 *   - accion:    Función JS a ejecutar al hacer clic (o NULL si es padre de dropdown)
 *   - activo:    'S' = visible, 'N' = oculto
 */
require_once 'modelos/DAO.php';

class MMenus {
    /** @var DAO Objeto de acceso a datos */
    private $dao;

    /**
     * Constructor - Inicializa la conexión a la BD
     */
    public function __construct() {
        $this->dao = new DAO();
    }

    /**
     * Obtener los menús de nivel 1 (opciones principales de la barra)
     * Son aquellos cuyo idPadre es NULL, ordenados por posición.
     * 
     * @return array Lista de menús nivel 1 con campos: idOpcion, etiqueta, accion, posicion
     */
    public function obtenerMenusNivel1() {
        $sql = "SELECT idOpcion, texto, accion, orden 
                FROM menus 
                WHERE idPadre IS NULL AND activo = 'S' 
                ORDER BY orden ASC";
        return $this->dao->consultar($sql);
    }

    /**
     * Obtener los submenús (nivel 2) de un menú padre específico
     * Son las opciones que aparecen en el desplegable de un menú de nivel 1.
     * 
     * @param int $idPadre ID del menú padre del cual obtener los submenús
     * @return array Lista de submenús con campos: idOpcion, texto, accion, orden
     */
    public function obtenerSubmenus($idPadre) {
        $idPadre = (int)$idPadre;
        $sql = "SELECT idOpcion, texto, accion, orden 
                FROM menus 
                WHERE idPadre = $idPadre AND activo = 'S' 
                ORDER BY orden ASC";
        return $this->dao->consultar($sql);
    }
}
?>
