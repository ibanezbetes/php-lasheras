<?php
/**
 * CPedidos.php - Controlador del módulo de Pedidos (Maestro-Detalle)
 * 
 * Gestiona las operaciones ajustadas al nuevo esquema de la base de datos:
 * - pedidos: idPedido, idUsuario, fechaPedido, fechaAlmacen, fechaEnvio, fechaRecibido, fechaFinalizado, transporte, direccion
 * - pedidosdetalles: idDetalle, idPedido, idProducto, cantidad, precioVenta
 */
require_once 'controladores/Controlador.php';
require_once 'vistas/Vista.php';
require_once 'modelos/MPedidos.php';

class CPedidos extends Controlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new MPedidos();
    }

    public function getVistaPedidosPrincipal($datos = array()) {
        Vista::render('vistas/Pedidos/VPedidosPrincipal.php');
    }

    public function getVistaListadoPedidos($datos = array()) {
        extract($datos);
        $usuario = isset($usuario) ? $usuario : '';
        $fecha   = isset($fecha)   ? $fecha   : '';
        $pagina  = isset($pagina)  ? (int)$pagina : 1;
        $tamPag  = isset($tam_pag) ? (int)$tam_pag : 15;

        // Preparar array de filtros para el modelo
        $filtros = array(
            'usuario' => $usuario,
            'fecha'   => $fecha
        );

        $totalRegistros = $this->modelo->contarPedidos($filtros);
        $pedidos = $this->modelo->obtenerPedidos($filtros, $pagina, $tamPag);

        if (count($pedidos) > 0) {
            echo '<div class="table-responsive"><table class="table table-striped table-hover">
                  <thead><tr>
                      <th>ID</th><th>Fecha Pedido</th><th>Usuario</th>
                      <th>Total</th><th>Estado</th><th>Acciones</th>
                  </tr></thead><tbody>';

            foreach ($pedidos as $p) {
                // Traducir código de estado
                $estado = ($p['estado'] == 'C') ? 'Completado' : 'Pendiente';

                echo '<tr class="align-middle" style="cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor=\'rgba(255,255,255,0.05)\'" onmouseout="this.style.backgroundColor=\'\'">
                        <td onclick="verDetallesPedido(' . $p['idPedido'] . ');">' . $p['idPedido'] . '</td>
                        <td onclick="verDetallesPedido(' . $p['idPedido'] . ');">' . $p['fechaPedido'] . '</td>
                        <td onclick="verDetallesPedido(' . $p['idPedido'] . ');">' . $p['nombre'] . ' ' . $p['apellido1'] . '</td>
                        <td onclick="verDetallesPedido(' . $p['idPedido'] . ');">' . number_format($p['total'], 2) . ' €</td>
                        <td onclick="verDetallesPedido(' . $p['idPedido'] . ');">' . $estado . '</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info me-1 text-white" 
                                    title="Ver detalles" onclick="verDetallesPedido(' . $p['idPedido'] . ');">👁️</button>
                            <button type="button" class="btn btn-sm btn-primary me-1" 
                                    title="Editar pedido" onclick="editarPedido(' . $p['idPedido'] . ');">✏️</button>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    title="Eliminar pedido" onclick="eliminarPedido(' . $p['idPedido'] . ');">❌</button>
                        </td>
                      </tr>';
            }
            echo '</tbody></table></div>';

            Vista::render('vistas/VPaginacion.php', array(
                'totalRegistros'  => $totalRegistros,
                'pagActual'       => $pagina,
                'tamPag'          => $tamPag,
                'funcionCallback' => 'buscarPedidos'
            ));
        } else {
            echo '<div class="alert alert-warning">No se encontraron pedidos</div>';
        }
    }

    public function obtenerPedido($datos = array()) {
        extract($datos);

        if (empty($idPedido)) {
            echo json_encode(['error' => 'ID de pedido requerido']);
            return;
        }

        $pedido = $this->modelo->obtenerPedidoPorId($idPedido);

        if ($pedido) {
            $pedido['detalles'] = $this->modelo->obtenerLineasPedido($idPedido);
            header('Content-Type: application/json');
            echo json_encode($pedido);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Pedido no encontrado']);
        }
    }

    public function crearPedido($datos = array()) {
        extract($datos);

        if (empty($idUsuario) || empty($fechaPedido)) {
            echo '<div class="alert alert-danger">Usuario y fecha del pedido son obligatorios</div>';
            return;
        }

        $detallesArray = isset($detalles) ? json_decode($detalles, true) : [];
        if (!is_array($detallesArray) || count($detallesArray) == 0) {
            echo '<div class="alert alert-danger">Debe agregar al menos un producto al pedido</div>';
            return;
        }

        $datosPedido = array(
            'idUsuario'      => $idUsuario,
            'fechaPedido'    => $fechaPedido,
            'fechaAlmacen'   => isset($fechaAlmacen) ? $fechaAlmacen : '',
            'fechaEnvio'     => isset($fechaEnvio) ? $fechaEnvio : '',
            'fechaRecibido'  => isset($fechaRecibido) ? $fechaRecibido : '',
            'fechaFinalizado'=> isset($fechaFinalizado) ? $fechaFinalizado : '',
            'transporte'     => isset($transporte) ? $transporte : '',
            'direccion'      => isset($direccion) ? $direccion : ''
        );

        $lineas = array();
        foreach ($detallesArray as $d) {
            if (isset($d['idProducto']) && isset($d['cantidad']) && isset($d['precioUnitario'])) {
                $lineas[] = array(
                    'idProducto'     => $d['idProducto'],
                    'cantidad'       => $d['cantidad'],
                    'precioUnitario' => $d['precioUnitario']
                );
            }
        }

        $idPedido = $this->modelo->insertarPedido($datosPedido, $lineas);

        echo $idPedido > 0
            ? '<div class="alert alert-success">Pedido creado exitosamente (ID: ' . $idPedido . ')</div>'
            : '<div class="alert alert-danger">Error al crear el pedido</div>';
    }

    public function actualizarPedido($datos = array()) {
        extract($datos);

        if (empty($idPedido) || empty($idUsuario) || empty($fechaPedido)) {
            echo '<div class="alert alert-danger">Datos incompletos</div>';
            return;
        }

        $detallesArray = isset($detalles) ? json_decode($detalles, true) : [];
        if (!is_array($detallesArray) || count($detallesArray) == 0) {
            echo '<div class="alert alert-danger">Debe agregar al menos un producto al pedido</div>';
            return;
        }

        $datosPedido = array(
            'idUsuario'      => $idUsuario,
            'fechaPedido'    => $fechaPedido,
            'fechaAlmacen'   => isset($fechaAlmacen) ? $fechaAlmacen : '',
            'fechaEnvio'     => isset($fechaEnvio) ? $fechaEnvio : '',
            'fechaRecibido'  => isset($fechaRecibido) ? $fechaRecibido : '',
            'fechaFinalizado'=> isset($fechaFinalizado) ? $fechaFinalizado : '',
            'transporte'     => isset($transporte) ? $transporte : '',
            'direccion'      => isset($direccion) ? $direccion : ''
        );

        $lineas = array();
        foreach ($detallesArray as $d) {
            if (isset($d['idProducto']) && isset($d['cantidad']) && isset($d['precioUnitario'])) {
                $lineas[] = array(
                    'idProducto'     => $d['idProducto'],
                    'cantidad'       => $d['cantidad'],
                    'precioUnitario' => $d['precioUnitario']
                );
            }
        }

        $resultado = $this->modelo->actualizarPedido($idPedido, $datosPedido, $lineas);

        echo $resultado
            ? '<div class="alert alert-success">Pedido actualizado exitosamente</div>'
            : '<div class="alert alert-danger">Error al actualizar el pedido</div>';
    }

    public function eliminarPedido($datos = array()) {
        extract($datos);

        if (empty($idPedido)) {
            echo '<div class="alert alert-danger">ID de pedido requerido</div>';
            return;
        }

        $resultado = $this->modelo->borrarPedido($idPedido);

        echo $resultado
            ? '<div class="alert alert-success">Pedido eliminado exitosamente</div>'
            : '<div class="alert alert-danger">Error al eliminar el pedido</div>';
    }

    public function getUsuariosJSON() {
        $usuarios = $this->modelo->obtenerUsuarios();
        header('Content-Type: application/json');
        echo json_encode($usuarios);
    }

    public function buscarUsuariosJSON($datos = array()) {
        $filtro = isset($datos['filtro']) ? $datos['filtro'] : '';
        $usuarios = $this->modelo->obtenerUsuariosFiltrados($filtro);
        header('Content-Type: application/json');
        echo json_encode($usuarios);
    }

    public function getProductosJSON() {
        $productos = $this->modelo->obtenerProductos();
        header('Content-Type: application/json');
        echo json_encode($productos);
    }
}
?>
