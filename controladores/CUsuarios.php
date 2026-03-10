<?php
/**
 * CUsuarios.php - Controlador del módulo de Usuarios
 * 
 * Gestiona todas las operaciones del módulo de usuarios:
 * - Mostrar la vista principal
 * - Listar usuarios con búsqueda y paginación
 * - Crear, editar y eliminar usuarios
 * 
 * Utiliza el DAO para acceso a base de datos y Vista para renderizar las vistas.
 * Sigue el patrón MVC: este controlador conecta el modelo (DAO) con las vistas.
 */
require_once 'controladores/Controlador.php';
require_once 'vistas/Vista.php';
require_once 'modelos/DAO.php';

class CUsuarios extends Controlador {
    /** @var DAO Objeto de acceso a datos */
    private $dao;

    /**
     * Constructor - Inicializa la conexión a la base de datos
     */
    public function __construct() {
        $this->dao = new DAO();
    }

    /**
     * Cargar la vista principal del módulo de usuarios
     * Renderiza el formulario de búsqueda y el contenedor de resultados.
     * 
     * @param array $datos Parámetros recibidos (no se usan en este método)
     */
    public function getVistaUsuariosPrincipal($datos = array()) {
        Vista::render('vistas/Usuarios/VUsuariosPrincipal.php');
    }

    /**
     * Buscar y listar usuarios con filtros y paginación
     * Se llama por AJAX desde usuarios.js → buscarUsuarios()
     * 
     * Parámetros esperados en $datos:
     *   - nombre:  Filtro por nombre (opcional)
     *   - email:   Filtro por email (opcional)
     *   - pagina:  Página actual (por defecto 1)
     *   - tam_pag: Registros por página (por defecto 15)
     * 
     * @param array $datos Parámetros de búsqueda y paginación
     */
    public function getVistaListadoUsuarios($datos = array()) {
        extract($datos);
        $nombre = isset($nombre) ? $nombre : '';
        $email  = isset($email)  ? $email  : '';
        $pagina = isset($pagina) ? (int)$pagina : 1;
        $tamPag = isset($tam_pag) ? (int)$tam_pag : 15;

        // ----- 1. Contar el TOTAL de registros (para la paginación) -----
        $sqlCount = "SELECT COUNT(*) as total FROM usuarios WHERE activo='S'";

        // Aplicar filtros de búsqueda
        $filtro = "";
        if ($nombre != '') $filtro .= " AND nombre LIKE '%$nombre%'";
        if ($email != '')  $filtro .= " AND mail LIKE '%$email%'";

        $sqlCount .= $filtro;
        $res = $this->dao->consultar($sqlCount);
        $totalRegistros = $res[0]['total'];

        // ----- 2. Obtener solo los registros de la página actual (LIMIT) -----
        $offset = ($pagina - 1) * $tamPag;

        $sql = "SELECT idUsuario, nombre, apellido1, apellido2, mail, movil, activo 
                FROM usuarios WHERE activo='S'";
        $sql .= $filtro;
        $sql .= " ORDER BY nombre, apellido1";
        $sql .= " LIMIT $offset, $tamPag";

        $usuarios = $this->dao->consultar($sql);

        // ----- 3. Generar la tabla HTML con los resultados -----
        if (count($usuarios) > 0) {
            echo '<div class="table-responsive"><table class="table table-striped table-hover">
                  <thead><tr>
                      <th>Nombre</th><th>Apellidos</th><th>Email</th><th>Móvil</th><th>Acciones</th>
                  </tr></thead><tbody>';

            foreach ($usuarios as $u) {
                echo '<tr>
                        <td>' . $u['nombre'] . '</td>
                        <td>' . $u['apellido1'] . ' ' . ($u['apellido2'] ?? '') . '</td>
                        <td>' . $u['mail'] . '</td>
                        <td>' . $u['movil'] . '</td>
                        <td>
                            <button class="btn btn-sm btn-primary me-1" 
                                    onclick="editarUsuario(' . $u['idUsuario'] . ');">✏️</button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="eliminarUsuario(' . $u['idUsuario'] . ',\'' . $u['nombre'] . '\');">❌</button>
                        </td>
                      </tr>';
            }
            echo '</tbody></table></div>';

            // ----- 4. Renderizar la paginación reutilizable -----
            Vista::render('vistas/VPaginacion.php', array(
                'totalRegistros'  => $totalRegistros,
                'pagActual'       => $pagina,
                'tamPag'          => $tamPag,
                'funcionCallback' => 'buscarUsuarios'  // Función JS que se llama al cambiar página
            ));

        } else {
            echo '<div class="alert alert-warning">No se encontraron usuarios</div>';
        }
    }

    /**
     * Obtener los datos de un usuario concreto (para edición)
     * Devuelve los datos en formato JSON.
     * 
     * @param array $datos Debe contener 'idUsuario'
     */
    public function obtenerUsuario($datos = array()) {
        extract($datos);
        $sql = "SELECT * FROM usuarios WHERE idUsuario = $idUsuario";
        $usuarios = $this->dao->consultar($sql);
        header('Content-Type: application/json');
        echo json_encode(count($usuarios) > 0 ? $usuarios[0] : ['error' => 'No encontrado']);
    }

    /**
     * Crear un nuevo usuario en la base de datos
     * Valida campos obligatorios antes de insertar.
     * 
     * @param array $datos Campos del formulario: nombre, apellido1, mail, login, pass, etc.
     */
    public function crearUsuario($datos = array()) {
        extract($datos);

        // Validar campos obligatorios
        if (empty($nombre) || empty($apellido1) || empty($mail) || empty($login) || empty($pass)) {
            echo '<div class="alert alert-danger">Campos obligatorios incompletos</div>';
            return;
        }

        // Encriptar contraseña con MD5 (en producción se recomienda usar password_hash)
        $passEncriptada = md5($pass);
        $fechaAlta = date('Y-m-d');

        $sql = "INSERT INTO usuarios (nombre, apellido1, apellido2, mail, movil, login, pass, sexo, fechaAlta, activo) 
                VALUES ('$nombre','$apellido1','$apellido2','$mail','$movil','$login','$passEncriptada','$sexo','$fechaAlta','S')";
        $id = $this->dao->insertar($sql);
        echo $id > 0
            ? '<div class="alert alert-success">Usuario creado exitosamente</div>'
            : '<div class="alert alert-danger">Error al crear el usuario</div>';
    }

    /**
     * Actualizar los datos de un usuario existente
     * 
     * @param array $datos Campos del formulario incluyendo 'idUsuario'
     */
    public function actualizarUsuario($datos = array()) {
        extract($datos);

        if (empty($idUsuario) || empty($nombre) || empty($apellido1) || empty($mail) || empty($login)) {
            echo '<div class="alert alert-danger">Campos obligatorios incompletos</div>';
            return;
        }

        $sql = "UPDATE usuarios SET nombre='$nombre', apellido1='$apellido1', apellido2='$apellido2', 
                mail='$mail', movil='$movil', login='$login', sexo='$sexo' 
                WHERE idUsuario=$idUsuario";
        $res = $this->dao->actualizar($sql);
        echo $res >= 0
            ? '<div class="alert alert-success">Usuario actualizado exitosamente</div>'
            : '<div class="alert alert-danger">Error al actualizar el usuario</div>';
    }

    /**
     * Eliminar un usuario (baja lógica: pone activo='N')
     * No permite eliminar al usuario administrador.
     * 
     * @param array $datos Debe contener 'idUsuario'
     */
    public function eliminarUsuario($datos = array()) {
        extract($datos);

        // Verificar que no sea el administrador
        $sqlCheck = "SELECT login FROM usuarios WHERE idUsuario = $idUsuario";
        $usuario = $this->dao->consultar($sqlCheck);

        if (!empty($usuario) && $usuario[0]['login'] === 'admin') {
            echo '<div class="alert alert-danger">No se puede eliminar al administrador principal</div>';
            return;
        }

        // Baja lógica: desactivar en vez de borrar
        $sql = "UPDATE usuarios SET activo='N' WHERE idUsuario=$idUsuario";
        $res = $this->dao->actualizar($sql);
        echo $res > 0
            ? '<div class="alert alert-success">Usuario eliminado exitosamente</div>'
            : '<div class="alert alert-danger">Error al eliminar el usuario</div>';
    }
}
?>
