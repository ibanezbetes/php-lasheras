<?php
/**
 * MPedidos.php - Modelo del módulo de Pedidos (Maestro-Detalle)
 * 
 * Gestiona toda la lógica de acceso a datos para pedidos y sus líneas de detalle.
 * Implementa operaciones transaccionales para garantizar la integridad de datos
 * cuando se insertan/actualizan pedidos junto con sus líneas.
 * 
 * Tablas que utiliza:
 *   - pedidos:        Cabecera del pedido (fecha, usuario, total, estado)
 *   - lineas_pedido:  Líneas de detalle (producto, cantidad, precio, subtotal)
 *   - usuarios:       Para JOINs y select de usuarios
 *   - productos:      Para JOINs y select de productos
 */
require_once 'modelos/DAO.php';

class MPedidos {
    /** @var DAO Objeto de acceso a datos */
    private $dao;

    /**
     * Constructor - Inicializa la conexión a la base de datos
     */
    public function __construct() {
        $this->dao = new DAO();
    }

    // =====================================================================
    // CONSULTAS DE LECTURA (SELECT)
    // =====================================================================

    /**
     * Obtener pedidos paginados con filtros opcionales
     * Hace JOIN con usuarios para mostrar el nombre del usuario en el listado.
     * 
     * @param array $filtros  Filtros: 'usuario' (nombre), 'fecha', 'estado'
     * @param int   $pagina   Número de página actual (empezando en 1)
     * @param int   $tamPag   Cantidad de registros por página
     * @return array          Array de pedidos con datos del usuario
     */
    public function obtenerPedidos($filtros, $pagina, $tamPag) {
        // Construir la cláusula WHERE según los filtros recibidos
        $where = "p.activo='S'";

        if (!empty($filtros['usuario'])) {
            $usuario = $this->dao->getConexion()->real_escape_string($filtros['usuario']);
            $where .= " AND (u.nombre LIKE '%$usuario%' OR u.apellido1 LIKE '%$usuario%')";
        }

        if (!empty($filtros['fecha'])) {
            $fecha = $this->dao->getConexion()->real_escape_string($filtros['fecha']);
            $where .= " AND p.fecha = '$fecha'";
        }

        if (!empty($filtros['estado'])) {
            $estado = $this->dao->getConexion()->real_escape_string($filtros['estado']);
            $where .= " AND p.estado = '$estado'";
        }

        // Calcular el desplazamiento (offset) para la paginación
        $offset = ($pagina - 1) * $tamPag;

        // Consulta con JOIN a usuarios y paginación con LIMIT
        $sql = "SELECT p.*, u.nombre, u.apellido1 
                FROM pedidos p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario 
                WHERE $where 
                ORDER BY p.fecha DESC, p.idPedido DESC 
                LIMIT $offset, $tamPag";

        return $this->dao->consultar($sql);
    }

    /**
     * Contar el total de pedidos que coinciden con los filtros
     * Necesario para calcular el número de páginas en la paginación.
     * 
     * @param array $filtros  Mismos filtros que obtenerPedidos()
     * @return int            Número total de pedidos que coinciden
     */
    public function contarPedidos($filtros) {
        // Construir WHERE (misma lógica que obtenerPedidos)
        $where = "p.activo='S'";

        if (!empty($filtros['usuario'])) {
            $usuario = $this->dao->getConexion()->real_escape_string($filtros['usuario']);
            $where .= " AND (u.nombre LIKE '%$usuario%' OR u.apellido1 LIKE '%$usuario%')";
        }

        if (!empty($filtros['fecha'])) {
            $fecha = $this->dao->getConexion()->real_escape_string($filtros['fecha']);
            $where .= " AND p.fecha = '$fecha'";
        }

        if (!empty($filtros['estado'])) {
            $estado = $this->dao->getConexion()->real_escape_string($filtros['estado']);
            $where .= " AND p.estado = '$estado'";
        }

        $sql = "SELECT COUNT(*) as total 
                FROM pedidos p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario 
                WHERE $where";

        $resultado = $this->dao->consultar($sql);
        return $resultado[0]['total'];
    }

    /**
     * Obtener un pedido concreto por su ID
     * Solo devuelve pedidos activos (activo='S').
     * 
     * @param int $idPedido  ID del pedido a buscar
     * @return array|null    Datos del pedido o null si no existe
     */
    public function obtenerPedidoPorId($idPedido) {
        $idPedido = (int)$idPedido;
        $sql = "SELECT p.*, u.nombre as u_nombre, u.apellido1 as u_apellido1 
                FROM pedidos p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario 
                WHERE p.idPedido=$idPedido AND p.activo='S'";
        $resultado = $this->dao->consultar($sql);
        return !empty($resultado) ? $resultado[0] : null;
    }

    /**
     * Obtener todas las líneas de detalle de un pedido
     * Incluye el nombre del producto mediante JOIN con la tabla productos.
     * 
     * @param int $idPedido  ID del pedido
     * @return array         Array de líneas con datos del producto
     */
    public function obtenerLineasPedido($idPedido) {
        $idPedido = (int)$idPedido;
        $sql = "SELECT lp.*, p.producto 
                FROM lineas_pedido lp
                INNER JOIN productos p ON lp.idProducto = p.idProducto
                WHERE lp.idPedido=$idPedido
                ORDER BY lp.idLinea";

        return $this->dao->consultar($sql);
    }

    // =====================================================================
    // OPERACIONES DE ESCRITURA (INSERT, UPDATE, DELETE) - TRANSACCIONALES
    // =====================================================================

    /**
     * Insertar un nuevo pedido con sus líneas de detalle (TRANSACCIONAL)
     * 
     * Proceso:
     * 1. Iniciar transacción
     * 2. Insertar cabecera del pedido (total = 0 inicialmente)
     * 3. Insertar cada línea de detalle (calculando subtotales)
     * 4. Actualizar el total del pedido sumando los subtotales
     * 5. Confirmar la transacción (o revertir si hay error)
     * 
     * @param array $datosPedido  Datos de la cabecera: fecha, idUsuario, estado
     * @param array $lineas       Array de líneas: [{idProducto, cantidad, precioUnitario}, ...]
     * @return int|false          ID del nuevo pedido o false si falla
     */
    public function insertarPedido($datosPedido, $lineas = array()) {
        try {
            // Paso 1: Iniciar transacción (todo o nada)
            $this->dao->iniciarTransaccion();

            // Paso 2: Preparar y escapar datos de la cabecera
            $fecha         = $this->dao->getConexion()->real_escape_string($datosPedido['fecha']);
            $idUsuario     = (int)$datosPedido['idUsuario'];
            $estado        = $this->dao->getConexion()->real_escape_string($datosPedido['estado']);
            $observaciones = isset($datosPedido['observaciones'])
                ? $this->dao->getConexion()->real_escape_string($datosPedido['observaciones'])
                : '';

            // Insertar la cabecera con total = 0 (se actualizará después)
            $sql = "INSERT INTO pedidos (fecha, idUsuario, estado, total, observaciones, activo) 
                    VALUES ('$fecha', $idUsuario, '$estado', 0.00, '$observaciones', 'S')";
            $idPedido = $this->dao->insertar($sql);

            if (!$idPedido) {
                throw new Exception("Error al insertar la cabecera del pedido");
            }

            // Paso 3: Insertar cada línea de detalle
            $totalPedido = 0;
            foreach ($lineas as $linea) {
                $idProducto     = (int)$linea['idProducto'];
                $cantidad       = (int)$linea['cantidad'];
                $precioUnitario = (float)$linea['precioUnitario'];
                $subtotal       = $cantidad * $precioUnitario;
                $totalPedido   += $subtotal;

                $sqlLinea = "INSERT INTO lineas_pedido (idPedido, idProducto, cantidad, precioUnitario, subtotal) 
                            VALUES ($idPedido, $idProducto, $cantidad, $precioUnitario, $subtotal)";
                $this->dao->insertar($sqlLinea);
            }

            // Paso 4: Actualizar el total del pedido con la suma de subtotales
            $sqlTotal = "UPDATE pedidos SET total=$totalPedido WHERE idPedido=$idPedido";
            $this->dao->actualizar($sqlTotal);

            // Paso 5: Confirmar la transacción (aplicar todos los cambios)
            $this->dao->confirmarTransaccion();

            return $idPedido;

        } catch (Exception $e) {
            // Si algo falla, revertir TODOS los cambios
            $this->dao->revertirTransaccion();
            error_log("Error en insertarPedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un pedido existente y sus líneas (TRANSACCIONAL)
     * 
     * Estrategia: actualizar cabecera, borrar TODAS las líneas antiguas,
     * insertar las nuevas líneas, y recalcular el total.
     * 
     * @param int   $idPedido     ID del pedido a actualizar
     * @param array $datosPedido  Nuevos datos de la cabecera
     * @param array $lineas       Nuevas líneas de detalle
     * @return bool               true si se actualizó correctamente, false si falló
     */
    public function actualizarPedido($idPedido, $datosPedido, $lineas = array()) {
        try {
            // Iniciar transacción
            $this->dao->iniciarTransaccion();

            $idPedido = (int)$idPedido;

            // Preparar datos de la cabecera
            $fecha         = $this->dao->getConexion()->real_escape_string($datosPedido['fecha']);
            $idUsuario     = (int)$datosPedido['idUsuario'];
            $estado        = $this->dao->getConexion()->real_escape_string($datosPedido['estado']);
            $observaciones = isset($datosPedido['observaciones'])
                ? $this->dao->getConexion()->real_escape_string($datosPedido['observaciones'])
                : '';

            // Actualizar la cabecera del pedido
            $sql = "UPDATE pedidos 
                    SET fecha='$fecha', idUsuario=$idUsuario, estado='$estado', observaciones='$observaciones' 
                    WHERE idPedido=$idPedido";
            $this->dao->actualizar($sql);

            // Borrar TODAS las líneas antiguas del pedido
            $this->borrarLineasPedido($idPedido);

            // Insertar las nuevas líneas y calcular el nuevo total
            $totalPedido = 0;
            foreach ($lineas as $linea) {
                $idProducto     = (int)$linea['idProducto'];
                $cantidad       = (int)$linea['cantidad'];
                $precioUnitario = (float)$linea['precioUnitario'];
                $subtotal       = $cantidad * $precioUnitario;
                $totalPedido   += $subtotal;

                $sqlLinea = "INSERT INTO lineas_pedido (idPedido, idProducto, cantidad, precioUnitario, subtotal) 
                            VALUES ($idPedido, $idProducto, $cantidad, $precioUnitario, $subtotal)";
                $this->dao->insertar($sqlLinea);
            }

            // Actualizar el total del pedido
            $sqlTotal = "UPDATE pedidos SET total=$totalPedido WHERE idPedido=$idPedido";
            $this->dao->actualizar($sqlTotal);

            // Confirmar la transacción
            $this->dao->confirmarTransaccion();

            return true;

        } catch (Exception $e) {
            // Revertir si hay error
            $this->dao->revertirTransaccion();
            error_log("Error en actualizarPedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un pedido (baja lógica: pone activo='N')
     * Las líneas de detalle se mantienen intactas.
     * 
     * @param int $idPedido  ID del pedido a eliminar
     * @return int           Número de filas afectadas
     */
    public function borrarPedido($idPedido) {
        $idPedido = (int)$idPedido;
        $sql = "UPDATE pedidos SET activo='N' WHERE idPedido=$idPedido";
        return $this->dao->actualizar($sql);
    }

    /**
     * Insertar una línea de detalle individual
     * Calcula automáticamente el subtotal (cantidad * precioUnitario).
     * 
     * @param array $datosLinea  Datos: idPedido, idProducto, cantidad, precioUnitario
     * @return int               ID de la nueva línea
     */
    public function insertarLineaPedido($datosLinea) {
        $idPedido       = (int)$datosLinea['idPedido'];
        $idProducto     = (int)$datosLinea['idProducto'];
        $cantidad       = (int)$datosLinea['cantidad'];
        $precioUnitario = (float)$datosLinea['precioUnitario'];
        $subtotal       = $cantidad * $precioUnitario;

        $sql = "INSERT INTO lineas_pedido (idPedido, idProducto, cantidad, precioUnitario, subtotal) 
                VALUES ($idPedido, $idProducto, $cantidad, $precioUnitario, $subtotal)";

        return $this->dao->insertar($sql);
    }

    /**
     * Borrar todas las líneas de detalle de un pedido
     * Se usa internamente al actualizar un pedido (borrar antiguas + insertar nuevas).
     * 
     * @param int $idPedido  ID del pedido
     * @return int           Número de líneas eliminadas
     */
    public function borrarLineasPedido($idPedido) {
        $idPedido = (int)$idPedido;
        $sql = "DELETE FROM lineas_pedido WHERE idPedido=$idPedido";
        return $this->dao->borrar($sql);
    }

    /**
     * Recalcular y actualizar el total de un pedido a partir de sus líneas
     * Útil si se modifican líneas individualmente.
     * 
     * @param int $idPedido  ID del pedido
     * @return float         Total calculado
     */
    public function calcularTotalPedido($idPedido) {
        $idPedido = (int)$idPedido;

        // Sumar los subtotales de todas las líneas
        $sql = "SELECT SUM(subtotal) as total FROM lineas_pedido WHERE idPedido=$idPedido";
        $resultado = $this->dao->consultar($sql);
        $total = $resultado[0]['total'] ?? 0;

        // Actualizar el campo total en la cabecera del pedido
        $sqlUpdate = "UPDATE pedidos SET total=$total WHERE idPedido=$idPedido";
        $this->dao->actualizar($sqlUpdate);

        return $total;
    }

    // =====================================================================
    // DATOS AUXILIARES (para los selects de los formularios)
    // =====================================================================

    /**
     * Obtener todos los usuarios activos (para el select de usuario en el formulario)
     * 
     * @return array Lista de usuarios con idUsuario, nombre, apellido1
     */
    public function obtenerUsuarios() {
        $sql = "SELECT idUsuario, nombre, apellido1 FROM usuarios WHERE activo='S' ORDER BY nombre";
        return $this->dao->consultar($sql);
    }

    /**
     * Obtener usuarios filtrados por nombre/apellido (max 15) para autocompletar
     * 
     * @param string $filtro Texto a buscar
     * @return array Lista de usuarios que coinciden
     */
    public function obtenerUsuariosFiltrados($filtro) {
        $filtro = $this->dao->getConexion()->real_escape_string($filtro);
        $sql = "SELECT idUsuario, nombre, apellido1 
                FROM usuarios 
                WHERE activo='S' 
                AND (nombre LIKE '%$filtro%' OR apellido1 LIKE '%$filtro%') 
                ORDER BY nombre LIMIT 15";
        return $this->dao->consultar($sql);
    }

    /**
     * Obtener todos los productos activos (para el select de producto en las líneas)
     * Devuelve los campos con los nombres originales de la BD para que coincidan
     * con lo que espera pedidos.js (p.producto, p.precioVenta).
     * 
     * @return array Lista de productos con idProducto, producto, precioVenta
     */
    public function obtenerProductos() {
        $sql = "SELECT idProducto, producto, precioVenta 
                FROM productos WHERE activo='S' ORDER BY producto";
        return $this->dao->consultar($sql);
    }
}
?>
