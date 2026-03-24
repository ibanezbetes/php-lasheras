<?php
/**
 * MPermisos.php - Modelo del módulo de Permisos
 * 
 * Gestiona el acceso a las tablas de permisos, roles y sus relaciones
 * con usuarios y opciones de menú.
 * 
 * Tablas: permisos, roles, permisosrol, permisosusuario, rolesusuario, menus
 */
require_once 'modelos/DAO.php';

class MPermisos {
    private $dao;
    public $idRolAdministrador = 1; // ID del rol Administrador

    public function __construct() {
        $this->dao = new DAO();
    }

    // ================================================================
    // FUNCIONES DE MENÚ
    // ================================================================

    /**
     * Obtener las opciones del menú organizadas jerárquicamente
     * Si se filtra por idOpcion, devuelve solo esa opción con sus subopciones
     * 
     * @param array $filtros Filtros opcionales: idOpcion
     * @return array Estructura jerárquica del menú
     */
    public function getDatosMenuTabla($filtros = array()) {
        $where = '';
        $conSubopciones = isset($filtros['conSubopciones']) ? $filtros['conSubopciones'] : 'S';
        
        if (isset($filtros['idOpcion']) && $filtros['idOpcion'] != '') {
            $idOpcion = (int)$filtros['idOpcion'];
            $where = " AND m.idOpcion = $idOpcion";
        }

        // Obtener opciones de nivel 1 (idPadre = 0 o NULL)
        $sql = "SELECT idOpcion, texto, accion, orden, activo, idPadre,
                       IFNULL(publica,'S') as publica
                FROM menus m
                WHERE (idPadre IS NULL OR idPadre = 0) $where
                ORDER BY orden";
        
        $opciones = $this->dao->consultar($sql);
        $menu = array();

        foreach ($opciones as $opcion) {
            // Para cada opción de nivel 1, buscar sus subopciones
            if ($conSubopciones == 'S') {
                $sqlSub = "SELECT idOpcion, texto, accion, orden, activo, idPadre,
                                  IFNULL(publica,'S') as publica
                           FROM menus 
                           WHERE idPadre = " . $opcion['idOpcion'] . "
                           ORDER BY orden";
                $subOpciones = $this->dao->consultar($sqlSub);
                $opcion['subOpciones'] = $subOpciones;
            }
            $menu[] = $opcion;
        }

        return $menu;
    }

    /**
     * Obtener los datos de una opción concreta del menú
     * 
     * @param array $datos Debe contener 'idOpcion'
     * @return array Datos de la opción
     */
    public function getDatosOpcion($datos) {
        $idOpcion = (int)$datos['idOpcion'];
        $sql = "SELECT idOpcion, texto, accion, orden, activo, idPadre,
                       IFNULL(publica,'S') as publica
                FROM menus 
                WHERE idOpcion = $idOpcion";
        $resultado = $this->dao->consultar($sql);
        return !empty($resultado) ? $resultado[0] : array();
    }

    /**
     * Insertar una nueva opción de menú
     * 
     * @param array $datos Campos: texto, accion, orden, activo, publica, idPadre, idOpcionAnterior
     * @return int ID de la nueva opción
     */
    public function insertarNuevaOpcion($datos) {
        $texto = $this->dao->getConexion()->real_escape_string($datos['texto']);
        $accion = $this->dao->getConexion()->real_escape_string($datos['accion']);
        $orden = (int)$datos['orden'];
        $activo = isset($datos['activo']) ? $datos['activo'] : 'S';
        $publica = isset($datos['publica']) ? $datos['publica'] : 'S';
        $idPadre = (isset($datos['idPadre']) && $datos['idPadre'] != '') ? (int)$datos['idPadre'] : 'NULL';

        $sql = "INSERT INTO menus (texto, accion, orden, activo, publica, idPadre)
                VALUES ('$texto', '$accion', $orden, '$activo', '$publica', $idPadre)";
        return $this->dao->insertar($sql);
    }

    /**
     * Actualizar una opción de menú existente
     * 
     * @param array $datos Campos: idOpcion, texto, accion, activo, publica
     * @return int Número de filas afectadas
     */
    public function actualizarOpcion($datos) {
        $idOpcion = (int)$datos['idOpcion'];
        $texto = $this->dao->getConexion()->real_escape_string($datos['texto']);
        $accion = $this->dao->getConexion()->real_escape_string($datos['accion']);
        $activo = isset($datos['activo']) ? $datos['activo'] : 'S';
        $publica = isset($datos['publica']) ? $datos['publica'] : 'S';

        $sql = "UPDATE menus 
                SET texto='$texto', accion='$accion', activo='$activo', publica='$publica'
                WHERE idOpcion = $idOpcion";
        return $this->dao->actualizar($sql);
    }

    /**
     * Eliminar una opción de menú y sus permisos asociados (CASCADE)
     * 
     * @param array $datos Debe contener 'idOpcion'
     * @return int Filas eliminadas
     */
    public function eliminarOpcion($datos) {
        $idOpcion = (int)$datos['idOpcion'];
        $sql = "DELETE FROM menus WHERE idOpcion = $idOpcion";
        return $this->dao->borrar($sql);
    }

    // ================================================================
    // FUNCIONES DE PERMISOS
    // ================================================================

    /**
     * Obtener los permisos del menú, indexados por idOpcion
     * 
     * @param array $filtros Filtros opcionales: idOpcion, idPermiso
     * @return array Permisos indexados: [idOpcion][idx] => registro_permiso
     */
    public function getPermisosMenu($filtros = array()) {
        $where = '';
        if (isset($filtros['idOpcion']) && $filtros['idOpcion'] != '') {
            $where .= " AND p.idOpcion = " . (int)$filtros['idOpcion'];
        }
        if (isset($filtros['idPermiso']) && $filtros['idPermiso'] != '') {
            $where .= " AND p.idPermiso = " . (int)$filtros['idPermiso'];
        }

        $sql = "SELECT p.idPermiso, p.idOpcion, p.numPermiso, p.permiso
                FROM permisos p
                WHERE 1=1 $where
                ORDER BY p.idOpcion, p.numPermiso";
        
        $resultados = $this->dao->consultar($sql);
        
        // Indexar por idOpcion
        $permisos = array();
        foreach ($resultados as $row) {
            $permisos[$row['idOpcion']][] = $row;
        }
        return $permisos;
    }

    /**
     * Insertar un nuevo permiso
     * 
     * @param array $datos Campos: idOpcion, numPermiso, permiso
     * @return int ID del nuevo permiso
     */
    public function insertarNuevoPermiso($datos) {
        $idOpcion = (int)$datos['idOpcion'];
        $numPermiso = (int)$datos['numPermiso'];
        $permiso = $this->dao->getConexion()->real_escape_string($datos['permiso']);

        $sql = "INSERT INTO permisos (idOpcion, numPermiso, permiso)
                VALUES ($idOpcion, $numPermiso, '$permiso')";
        return $this->dao->insertar($sql);
    }

    /**
     * Actualizar un permiso existente
     */
    public function actualizarPermiso($datos) {
        $idPermiso = (int)$datos['idPermiso'];
        $numPermiso = (int)$datos['numPermiso'];
        $permiso = $this->dao->getConexion()->real_escape_string($datos['permiso']);

        $sql = "UPDATE permisos 
                SET numPermiso=$numPermiso, permiso='$permiso'
                WHERE idPermiso = $idPermiso";
        return $this->dao->actualizar($sql);
    }

    /**
     * Eliminar un permiso por su ID
     */
    public function eliminarPermisoPorId($datos) {
        $idPermiso = (int)$datos['idPermiso'];
        $sql = "DELETE FROM permisos WHERE idPermiso = $idPermiso";
        return $this->dao->borrar($sql);
    }

    // ================================================================
    // FUNCIONES DE ROLES
    // ================================================================

    /**
     * Obtener roles, opcionalmente filtrados
     * 
     * @param array $filtros Filtros: idRol, idUsuario (para filtrar roles de un usuario)
     * @return array Roles indexados por idRol
     */
    public function getRoles($filtros = array()) {
        $where = '';
        $join = '';

        if (isset($filtros['idRol']) && $filtros['idRol'] != '') {
            $where .= " AND r.idRol = " . (int)$filtros['idRol'];
        }
        if (isset($filtros['idUsuario']) && $filtros['idUsuario'] != '') {
            $join .= " INNER JOIN rolesusuario ru ON r.idRol = ru.idRol";
            $where .= " AND ru.idUsuario = " . (int)$filtros['idUsuario'];
        }

        $sql = "SELECT r.idRol, r.rol
                FROM roles r $join
                WHERE 1=1 $where
                ORDER BY r.rol";
        
        $resultados = $this->dao->consultar($sql);
        
        // Indexar por idRol
        $roles = array();
        foreach ($resultados as $row) {
            $roles[$row['idRol']] = $row;
        }
        return $roles;
    }

    /**
     * Insertar un nuevo rol
     */
    public function insertarNuevoRol($datos) {
        $rol = $this->dao->getConexion()->real_escape_string($datos['rol']);
        $sql = "INSERT INTO roles (rol) VALUES ('$rol')";
        return $this->dao->insertar($sql);
    }

    /**
     * Actualizar un rol existente
     */
    public function actualizarRol($datos) {
        $idRol = (int)$datos['idRol'];
        $rol = $this->dao->getConexion()->real_escape_string($datos['rol']);
        $sql = "UPDATE roles SET rol='$rol' WHERE idRol = $idRol";
        return $this->dao->actualizar($sql);
    }

    /**
     * Eliminar un rol
     */
    public function eliminarRol($datos) {
        $idRol = (int)$datos['idRol'];
        $sql = "DELETE FROM roles WHERE idRol = $idRol";
        return $this->dao->borrar($sql);
    }

    // ================================================================
    // FUNCIONES DE PERMISOS-ROL
    // ================================================================

    /**
     * Obtener los permisos de un rol, indexados por idPermiso
     * 
     * @param array $datos Debe contener 'idRol'
     * @return array Permisos del rol: [idPermiso] => idPermiso
     */
    public function getPermisosRol($datos) {
        $idRol = (int)$datos['idRol'];
        $sql = "SELECT pr.idPermiso
                FROM permisosrol pr
                WHERE pr.idRol = $idRol";
        
        $resultados = $this->dao->consultar($sql);
        $permisos = array();
        foreach ($resultados as $row) {
            $permisos[$row['idPermiso']] = $row['idPermiso'];
        }
        return $permisos;
    }

    /**
     * Asignar un permiso a un rol
     */
    public function insertarPermisoRol($datos) {
        $idPermiso = (int)$datos['idPermiso'];
        $idRol = (int)$datos['idRol'];
        $sql = "INSERT INTO permisosrol (idPermiso, idRol) VALUES ($idPermiso, $idRol)";
        return $this->dao->insertar($sql);
    }

    /**
     * Poner permiso a rol (alias para insertar)
     */
    public function ponerPermisoRol($datos) {
        return $this->insertarPermisoRol($datos);
    }

    /**
     * Quitar permiso de rol
     */
    public function quitarPermisoRol($datos) {
        $idPermiso = (int)$datos['idPermiso'];
        $idRol = (int)$datos['idRol'];
        $sql = "DELETE FROM permisosrol WHERE idPermiso=$idPermiso AND idRol=$idRol";
        return $this->dao->borrar($sql);
    }

    // ================================================================
    // FUNCIONES DE ROLES-USUARIO
    // ================================================================

    /**
     * Asignar un rol a un usuario
     */
    public function setRolUsuario($datos) {
        $idRol = (int)$datos['idRol'];
        $idUsuario = (int)$datos['idUsuario'];
        $sql = "INSERT INTO rolesusuario (idRol, idUsuario) VALUES ($idRol, $idUsuario)";
        return $this->dao->insertar($sql);
    }

    /**
     * Quitar un rol de un usuario
     */
    public function eliminarRolUsuario($datos) {
        $idRol = (int)$datos['idRol'];
        $idUsuario = (int)$datos['idUsuario'];
        $sql = "DELETE FROM rolesusuario WHERE idRol=$idRol AND idUsuario=$idUsuario";
        return $this->dao->borrar($sql);
    }

    // ================================================================
    // FUNCIONES DE PERMISOS-USUARIO
    // ================================================================

    /**
     * Obtener los permisos de un usuario (directos), indexados por idPermiso
     */
    public function getPermisosUsuario($datos) {
        $idUsuario = (int)$datos['idUsuario'];
        $sql = "SELECT pu.idPermiso
                FROM permisosusuario pu
                WHERE pu.idUsuario = $idUsuario";
        
        $resultados = $this->dao->consultar($sql);
        $permisos = array();
        foreach ($resultados as $row) {
            $permisos[$row['idPermiso']] = $row['idPermiso'];
        }
        return $permisos;
    }

    /**
     * Obtener los permisos de un usuario a través de sus roles
     * Formato: [idPermiso][idRol] = idRol
     */
    public function getPermisosUsuarioPorRoles($datos) {
        $idUsuario = (int)$datos['idUsuario'];
        $sql = "SELECT pr.idPermiso, pr.idRol
                FROM permisosrol pr
                INNER JOIN rolesusuario ru ON pr.idRol = ru.idRol
                WHERE ru.idUsuario = $idUsuario";
        
        $resultados = $this->dao->consultar($sql);
        $permisos = array();
        foreach ($resultados as $row) {
            $permisos[$row['idPermiso']][$row['idRol']] = $row['idRol'];
        }
        return $permisos;
    }

    /**
     * Poner permiso directo a un usuario
     */
    public function ponerPermisoUsuario($datos) {
        $idPermiso = (int)$datos['idPermiso'];
        $idUsuario = (int)$datos['idUsuario'];
        $sql = "INSERT INTO permisosusuario (idPermiso, idUsuario) VALUES ($idPermiso, $idUsuario)";
        return $this->dao->insertar($sql);
    }

    /**
     * Quitar permiso directo de un usuario
     */
    public function quitarPermisoUsuario($datos) {
        $idPermiso = (int)$datos['idPermiso'];
        $idUsuario = (int)$datos['idUsuario'];
        $sql = "DELETE FROM permisosusuario WHERE idPermiso=$idPermiso AND idUsuario=$idUsuario";
        return $this->dao->borrar($sql);
    }

    // ================================================================
    // FUNCIONES PARA OBTENER PERMISOS DEL USUARIO LOGUEADO
    // ================================================================

    /**
     * Obtener TODOS los permisos de un usuario (directos + por roles)
     * Devuelve un array con formato: [idOpcion][numPermiso] = true
     * Útil para filtrar el menú y condicionar operaciones.
     * 
     * @param int $idUsuario ID del usuario
     * @return array Permisos combinados
     */
    public function getPermisosCombinadosUsuario($idUsuario) {
        $idUsuario = (int)$idUsuario;
        
        // Permisos directos del usuario
        $sql = "SELECT p.idOpcion, p.numPermiso, p.idPermiso
                FROM permisos p
                INNER JOIN permisosusuario pu ON p.idPermiso = pu.idPermiso
                WHERE pu.idUsuario = $idUsuario
                UNION
                SELECT p.idOpcion, p.numPermiso, p.idPermiso
                FROM permisos p
                INNER JOIN permisosrol pr ON p.idPermiso = pr.idPermiso
                INNER JOIN rolesusuario ru ON pr.idRol = ru.idRol
                WHERE ru.idUsuario = $idUsuario";
        
        $resultados = $this->dao->consultar($sql);
        $permisos = array();
        foreach ($resultados as $row) {
            $permisos[$row['idOpcion']][$row['numPermiso']] = true;
        }
        return $permisos;
    }

    /**
     * Obtener las opciones de menú a las que el usuario tiene permiso
     * (al menos un permiso en esa opción)
     * 
     * @param int $idUsuario ID del usuario
     * @return array Array de idOpcion permitidos
     */
    public function getOpcionesPermitidasUsuario($idUsuario) {
        $permisos = $this->getPermisosCombinadosUsuario($idUsuario);
        return array_keys($permisos);
    }
}
?>
