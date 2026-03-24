<?php
/**
 * MUsuarios.php - Modelo del módulo de Usuarios
 * 
 * Proporciona acceso a la tabla 'usuarios' de la base de datos.
 * Se usa tanto en el módulo de usuarios como en el de permisos.
 */
require_once 'modelos/DAO.php';

class MUsuarios {
    private $dao;

    public function __construct() {
        $this->dao = new DAO();
    }

    /**
     * Buscar usuarios con filtros opcionales
     * 
     * @param array $filtros Filtros opcionales: idUsuario, nombre, activo
     * @return array Lista de usuarios
     */
    public function buscarUsuarios($filtros = array()) {
        $where = " WHERE activo='S'";

        if (isset($filtros['idUsuario']) && $filtros['idUsuario'] != '') {
            $idUsuario = (int)$filtros['idUsuario'];
            $where .= " AND idUsuario = $idUsuario";
        }
        if (isset($filtros['nombre']) && $filtros['nombre'] != '') {
            $nombre = $this->dao->getConexion()->real_escape_string($filtros['nombre']);
            $where .= " AND (nombre LIKE '%$nombre%' OR apellido1 LIKE '%$nombre%')";
        }

        $sql = "SELECT idUsuario, nombre, apellido1, apellido2, mail, movil, login, activo
                FROM usuarios $where
                ORDER BY apellido1, apellido2, nombre";

        return $this->dao->consultar($sql);
    }

    /**
     * Obtener un usuario por su login
     * 
     * @param string $login Login del usuario
     * @return array|null Datos del usuario o null
     */
    public function obtenerUsuarioPorLogin($login) {
        $login = $this->dao->getConexion()->real_escape_string($login);
        $sql = "SELECT idUsuario, nombre, apellido1, apellido2, login
                FROM usuarios 
                WHERE login = '$login' AND activo='S'";
        $resultado = $this->dao->consultar($sql);
        return !empty($resultado) ? $resultado[0] : null;
    }
}
?>
