<?php
/**
 * DAO.php - Data Access Object (Objeto de Acceso a Datos)
 * 
 * Esta clase centraliza toda la comunicación con la base de datos MySQL.
 * Proporciona métodos genéricos para realizar consultas SELECT, INSERT,
 * UPDATE y DELETE, así como manejo de transacciones.
 * 
 * Todos los modelos del proyecto (MPedidos, MMenus, etc.) utilizan
 * esta clase para acceder a la base de datos.
 */

// ---------------------------------------------------------------
// Configuración de conexión a la base de datos
// Modificar estos valores según el entorno (local, producción, etc.)
// ---------------------------------------------------------------
define('HOST', '127.0.0.1');    // Servidor de la BD
define('USER', 'root');         // Usuario de la BD
define('PASS', '');             // Contraseña de la BD
define('DB',   'db_di25');      // Nombre de la base de datos

class DAO {
    /** @var mysqli Conexión activa a la base de datos */
    private $conexion;

    /**
     * Constructor - Establece la conexión con MySQL
     * Si la conexión falla, detiene la ejecución mostrando el error.
     */
    public function __construct() {
        $this->conexion = new mysqli(HOST, USER, PASS, DB);

        if ($this->conexion->connect_errno) {
            die('Error de conexión: ' . $this->conexion->connect_error);
        }
    }

    /**
     * Ejecutar una consulta SELECT
     * 
     * @param string $SQL  Consulta SQL de tipo SELECT
     * @return array        Array de arrays asociativos con cada fila del resultado
     * 
     * Ejemplo: $dao->consultar("SELECT * FROM usuarios WHERE activo='S'");
     */
    public function consultar($SQL) {
        $res = $this->conexion->query($SQL, MYSQLI_USE_RESULT);
        $filas = array();

        if ($this->conexion->errno) {
            die('Error en consulta: ' . $this->conexion->error);
        } else {
            // Recorrer los resultados y almacenarlos como arrays asociativos
            while ($reg = $res->fetch_assoc()) {
                $filas[] = $reg;
            }
        }
        return $filas;
    }

    /**
     * Ejecutar una sentencia INSERT
     * 
     * @param string $SQL  Consulta SQL de tipo INSERT
     * @return int          ID autogenerado del nuevo registro insertado
     * 
     * Ejemplo: $dao->insertar("INSERT INTO usuarios (nombre) VALUES ('Ana')");
     */
    public function insertar($SQL) {
        $this->conexion->query($SQL, MYSQLI_USE_RESULT);

        if ($this->conexion->connect_errno) {
            die('Error en BD: ' . $SQL);
        } else {
            return $this->conexion->insert_id;
        }
    }

    /**
     * Ejecutar una sentencia UPDATE
     * 
     * @param string $SQL  Consulta SQL de tipo UPDATE
     * @return int          Número de filas modificadas
     * 
     * Ejemplo: $dao->actualizar("UPDATE usuarios SET nombre='Ana' WHERE idUsuario=1");
     */
    public function actualizar($SQL) {
        $this->conexion->query($SQL, MYSQLI_USE_RESULT);

        if ($this->conexion->connect_errno) {
            die('Error en BD: ' . $SQL);
        } else {
            return $this->conexion->affected_rows;
        }
    }

    /**
     * Ejecutar una sentencia DELETE
     * 
     * @param string $SQL  Consulta SQL de tipo DELETE
     * @return int          Número de filas eliminadas
     * 
     * Ejemplo: $dao->borrar("DELETE FROM lineas_pedido WHERE idPedido=5");
     */
    public function borrar($SQL) {
        $this->conexion->query($SQL);
        return $this->conexion->affected_rows;
    }

    // ---------------------------------------------------------------
    // Métodos para transacciones
    // Necesarios para operaciones que afectan a varias tablas
    // (ej: insertar pedido + sus líneas de detalle)
    // ---------------------------------------------------------------

    /**
     * Iniciar una transacción
     * Las siguientes operaciones no se aplicarán hasta hacer commit
     */
    public function iniciarTransaccion() {
        $this->conexion->begin_transaction();
    }

    /**
     * Confirmar (commit) la transacción actual
     * Aplica todos los cambios realizados desde iniciarTransaccion()
     */
    public function confirmarTransaccion() {
        $this->conexion->commit();
    }

    /**
     * Revertir (rollback) la transacción actual
     * Deshace todos los cambios realizados desde iniciarTransaccion()
     */
    public function revertirTransaccion() {
        $this->conexion->rollback();
    }

    /**
     * Obtener el objeto conexión mysqli
     * Útil para operaciones avanzadas como real_escape_string()
     * 
     * @return mysqli  Objeto de conexión activo
     */
    public function getConexion() {
        return $this->conexion;
    }
}
?>
