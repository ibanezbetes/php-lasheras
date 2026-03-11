<?php
/**
 * MPedidos.php - Modelo del módulo de Pedidos (Maestro-Detalle)
 * Adaptado a la nueva estructura:
 *   - pedidos: idPedido, idUsuario, fechaPedido, fechaAlmacen, fechaEnvio, fechaRecibido, fechaFinalizado, transporte, direccion
 *   - pedidosdetalles: idDetalle, idPedido, idProducto, cantidad, precioVenta
 */
require_once 'modelos/DAO.php';

class MPedidos {
    private $dao;

    public function __construct() {
        $this->dao = new DAO();
    }

    public function obtenerPedidos($filtros, $pagina, $tamPag) {
        $where = "1=1";

        if (!empty($filtros['usuario'])) {
            $usuario = $this->dao->getConexion()->real_escape_string($filtros['usuario']);
            $where .= " AND (u.nombre LIKE '%$usuario%' OR u.apellido1 LIKE '%$usuario%')";
        }

        if (!empty($filtros['fecha'])) {
            $fecha = $this->dao->getConexion()->real_escape_string($filtros['fecha']);
            $where .= " AND DATE(p.fechaPedido) = '$fecha'";
        }

        $offset = ($pagina - 1) * $tamPag;

        // Calcular el estado basado en fechaFinalizado (si no es nulo/0, es C Completado, si no P Pendiente)
        // Calcular el total desde pedidosdetalles
        $sql = "SELECT p.*, u.nombre, u.apellido1,
                       (SELECT SUM(cantidad * precioVenta) FROM pedidosdetalles pd WHERE pd.idPedido = p.idPedido) as total,
                       IF(p.fechaFinalizado AND p.fechaFinalizado != '0000-00-00 00:00:00', 'C', 'P') as estado
                FROM pedidos p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario 
                WHERE $where 
                ORDER BY p.fechaPedido DESC, p.idPedido DESC 
                LIMIT $offset, $tamPag";

        return $this->dao->consultar($sql);
    }

    public function contarPedidos($filtros) {
        $where = "1=1";

        if (!empty($filtros['usuario'])) {
            $usuario = $this->dao->getConexion()->real_escape_string($filtros['usuario']);
            $where .= " AND (u.nombre LIKE '%$usuario%' OR u.apellido1 LIKE '%$usuario%')";
        }

        if (!empty($filtros['fecha'])) {
            $fecha = $this->dao->getConexion()->real_escape_string($filtros['fecha']);
            $where .= " AND DATE(p.fechaPedido) = '$fecha'";
        }

        $sql = "SELECT COUNT(*) as total 
                FROM pedidos p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario 
                WHERE $where";

        $resultado = $this->dao->consultar($sql);
        return $resultado[0]['total'];
    }

    public function obtenerPedidoPorId($idPedido) {
        $idPedido = (int)$idPedido;
        $sql = "SELECT p.*, u.nombre as u_nombre, u.apellido1 as u_apellido1,
                       IF(p.fechaFinalizado AND p.fechaFinalizado != '0000-00-00 00:00:00', 'C', 'P') as estado
                FROM pedidos p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario 
                WHERE p.idPedido=$idPedido";
        $resultado = $this->dao->consultar($sql);
        return !empty($resultado) ? $resultado[0] : null;
    }

    public function obtenerLineasPedido($idPedido) {
        $idPedido = (int)$idPedido;
        // Se renombran para mantener compatibilidad con el JS anterior que usaba idLinea y precioUnitario
        $sql = "SELECT pd.idDetalle as idLinea, pd.idPedido, pd.idProducto, pd.cantidad, pd.precioVenta as precioUnitario, p.producto 
                FROM pedidosdetalles pd
                INNER JOIN productos p ON pd.idProducto = p.idProducto
                WHERE pd.idPedido=$idPedido
                ORDER BY pd.idDetalle";

        return $this->dao->consultar($sql);
    }

    public function insertarPedido($datosPedido, $lineas = array()) {
        try {
            $this->dao->iniciarTransaccion();

            $fechaPedido      = $this->dao->getConexion()->real_escape_string($datosPedido['fechaPedido']);
            $fechaAlmacen     = $this->dao->getConexion()->real_escape_string($datosPedido['fechaAlmacen'] ?: '0000-00-00 00:00:00');
            $fechaEnvio       = $this->dao->getConexion()->real_escape_string($datosPedido['fechaEnvio'] ?: '0000-00-00 00:00:00');
            $fechaRecibido    = $this->dao->getConexion()->real_escape_string($datosPedido['fechaRecibido'] ?: '0000-00-00 00:00:00');
            $fechaFinalizado  = $this->dao->getConexion()->real_escape_string($datosPedido['fechaFinalizado'] ?: '0000-00-00 00:00:00');
            $transporte       = $this->dao->getConexion()->real_escape_string($datosPedido['transporte'] ?: '');
            $direccion        = $this->dao->getConexion()->real_escape_string($datosPedido['direccion'] ?: '');
            $idUsuario        = (int)$datosPedido['idUsuario'];

            $sql = "INSERT INTO pedidos (idUsuario, fechaPedido, fechaAlmacen, fechaEnvio, fechaRecibido, fechaFinalizado, transporte, direccion) 
                    VALUES ($idUsuario, '$fechaPedido', '$fechaAlmacen', '$fechaEnvio', '$fechaRecibido', '$fechaFinalizado', '$transporte', '$direccion')";
            $idPedido = $this->dao->insertar($sql);

            if (!$idPedido) {
                throw new Exception("Error al insertar la cabecera del pedido");
            }

            foreach ($lineas as $linea) {
                $idProducto     = (int)$linea['idProducto'];
                $cantidad       = (int)$linea['cantidad'];
                $precioUnitario = (float)$linea['precioUnitario'];

                $sqlLinea = "INSERT INTO pedidosdetalles (idPedido, idProducto, cantidad, precioVenta) 
                             VALUES ($idPedido, $idProducto, $cantidad, $precioUnitario)";
                $this->dao->insertar($sqlLinea);
            }

            $this->dao->confirmarTransaccion();
            return $idPedido;

        } catch (Exception $e) {
            $this->dao->revertirTransaccion();
            error_log("Error en insertarPedido: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarPedido($idPedido, $datosPedido, $lineas = array()) {
        try {
            $this->dao->iniciarTransaccion();

            $idPedido = (int)$idPedido;

            $fechaPedido      = $this->dao->getConexion()->real_escape_string($datosPedido['fechaPedido']);
            $fechaAlmacen     = $this->dao->getConexion()->real_escape_string($datosPedido['fechaAlmacen'] ?: '0000-00-00 00:00:00');
            $fechaEnvio       = $this->dao->getConexion()->real_escape_string($datosPedido['fechaEnvio'] ?: '0000-00-00 00:00:00');
            $fechaRecibido    = $this->dao->getConexion()->real_escape_string($datosPedido['fechaRecibido'] ?: '0000-00-00 00:00:00');
            $fechaFinalizado  = $this->dao->getConexion()->real_escape_string($datosPedido['fechaFinalizado'] ?: '0000-00-00 00:00:00');
            $transporte       = $this->dao->getConexion()->real_escape_string($datosPedido['transporte'] ?: '');
            $direccion        = $this->dao->getConexion()->real_escape_string($datosPedido['direccion'] ?: '');
            $idUsuario        = (int)$datosPedido['idUsuario'];

            $sql = "UPDATE pedidos 
                    SET idUsuario=$idUsuario, fechaPedido='$fechaPedido', fechaAlmacen='$fechaAlmacen', fechaEnvio='$fechaEnvio', fechaRecibido='$fechaRecibido', fechaFinalizado='$fechaFinalizado', transporte='$transporte', direccion='$direccion'
                    WHERE idPedido=$idPedido";
            $this->dao->actualizar($sql);

            $this->borrarLineasPedido($idPedido);

            foreach ($lineas as $linea) {
                $idProducto     = (int)$linea['idProducto'];
                $cantidad       = (int)$linea['cantidad'];
                $precioUnitario = (float)$linea['precioUnitario'];

                $sqlLinea = "INSERT INTO pedidosdetalles (idPedido, idProducto, cantidad, precioVenta) 
                             VALUES ($idPedido, $idProducto, $cantidad, $precioUnitario)";
                $this->dao->insertar($sqlLinea);
            }

            $this->dao->confirmarTransaccion();

            return true;

        } catch (Exception $e) {
            $this->dao->revertirTransaccion();
            error_log("Error en actualizarPedido: " . $e->getMessage());
            return false;
        }
    }

    public function borrarPedido($idPedido) {
        $idPedido = (int)$idPedido;
        // Eliminación física
        $this->borrarLineasPedido($idPedido);
        $sql = "DELETE FROM pedidos WHERE idPedido=$idPedido";
        return $this->dao->borrar($sql);
    }

    public function borrarLineasPedido($idPedido) {
        $idPedido = (int)$idPedido;
        $sql = "DELETE FROM pedidosdetalles WHERE idPedido=$idPedido";
        return $this->dao->borrar($sql);
    }

    public function obtenerUsuarios() {
        $sql = "SELECT idUsuario, nombre, apellido1 FROM usuarios WHERE activo='S' ORDER BY nombre";
        return $this->dao->consultar($sql);
    }

    public function obtenerUsuariosFiltrados($filtro) {
        $filtro = $this->dao->getConexion()->real_escape_string($filtro);
        $sql = "SELECT idUsuario, nombre, apellido1 
                FROM usuarios 
                WHERE activo='S' 
                AND (nombre LIKE '%$filtro%' OR apellido1 LIKE '%$filtro%') 
                ORDER BY nombre LIMIT 15";
        return $this->dao->consultar($sql);
    }

    public function obtenerProductos() {
        $sql = "SELECT idProducto, producto, precioVenta 
                FROM productos WHERE activo='S' ORDER BY producto";
        return $this->dao->consultar($sql);
    }
}
?>
