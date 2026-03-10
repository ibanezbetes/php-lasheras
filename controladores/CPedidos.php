<?php
/**
 * CPedidos.php - Controlador del módulo de Pedidos (Maestro-Detalle)
 * 
 * Este módulo implementa un patrón maestro-detalle:
 * - Maestro: tabla 'pedidos' (cabecera del pedido)
 * - Detalle: tabla 'lineas_pedido' (líneas/productos del pedido)
 * 
 * Gestiona las siguientes operaciones:
 * - Mostrar la vista principal de pedidos
 * - Listar pedidos con filtros y paginación
 * - Obtener detalles de un pedido (JSON, para edición)
 * - Crear pedidos con sus líneas de detalle (transaccional)
 * - Actualizar pedidos y sus líneas (transaccional)
 * - Eliminar pedidos (baja lógica)
 * - Proporcionar listas de usuarios/productos en JSON (para los selects del formulario)
 */
require_once 'controladores/Controlador.php';
require_once 'vistas/Vista.php';
require_once 'modelos/MPedidos.php';

class CPedidos extends Controlador {
    /** @var MPedidos Modelo de acceso a datos de pedidos */
    private $modelo;

    /**
     * Constructor - Inicializa el modelo de pedidos
     */
    public function __construct() {
        $this->modelo = new MPedidos();
    }

    /**
     * Cargar la vista principal del módulo de pedidos
     * Muestra el formulario de búsqueda y los contenedores de resultados/formulario.
     * 
     * @param array $datos Parámetros recibidos (no se usan)
     */
    public function getVistaPedidosPrincipal($datos = array()) {
        Vista::render('vistas/Pedidos/VPedidosPrincipal.php');
    }

    /**
     * Buscar y listar pedidos con filtros y paginación
     * Se llama desde pedidos.js → buscarPedidos()
     * 
     * Parámetros esperados en $datos:
     *   - usuario: Filtro por nombre de usuario (opcional)
     *   - fecha:   Filtro por fecha del pedido (opcional)
     *   - pagina:  Página actual (por defecto 1)
     *   - tam_pag: Registros por página (por defecto 15)
     * 
     * @param array $datos Parámetros de búsqueda y paginación
     */
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

        // Obtener el total de registros (para calcular la paginación)
        $totalRegistros = $this->modelo->contarPedidos($filtros);

        // Obtener solo los pedidos de la página actual
        $pedidos = $this->modelo->obtenerPedidos($filtros, $pagina, $tamPag);

        // ----- Generar la tabla HTML con los resultados -----
        if (count($pedidos) > 0) {
            echo '<div class="table-responsive"><table class="table table-striped table-hover">
                  <thead><tr>
                      <th>ID</th><th>Fecha</th><th>Usuario</th>
                      <th>Total</th><th>Estado</th><th>Acciones</th>
                  </tr></thead><tbody>';

            foreach ($pedidos as $p) {
                // Traducir código de estado a texto legible
                $estado = ($p['estado'] == 'P') ? 'Pendiente' : (($p['estado'] == 'C') ? 'Completado' : $p['estado']);

                echo '<tr>
                        <td>' . $p['idPedido'] . '</td>
                        <td>' . $p['fecha'] . '</td>
                        <td>' . $p['nombre'] . ' ' . $p['apellido1'] . '</td>
                        <td>' . number_format($p['total'], 2) . ' €</td>
                        <td>' . $estado . '</td>
                        <td>
                            <button class="btn btn-sm btn-primary me-1" 
                                    onclick="editarPedido(' . $p['idPedido'] . ');">✏️</button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="eliminarPedido(' . $p['idPedido'] . ');">❌</button>
                        </td>
                      </tr>';
            }
            echo '</tbody></table></div>';

            // Renderizar componente de paginación reutilizable
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

    /**
     * Obtener los datos completos de un pedido (cabecera + líneas)
     * Devuelve JSON para que pedidos.js pueda rellenar el formulario de edición.
     * 
     * @param array $datos Debe contener 'idPedido'
     */
    public function obtenerPedido($datos = array()) {
        extract($datos);

        if (empty($idPedido)) {
            echo json_encode(['error' => 'ID de pedido requerido']);
            return;
        }

        // Obtener la cabecera del pedido
        $pedido = $this->modelo->obtenerPedidoPorId($idPedido);

        if ($pedido) {
            // Añadir las líneas de detalle al pedido
            $pedido['detalles'] = $this->modelo->obtenerLineasPedido($idPedido);
            header('Content-Type: application/json');
            echo json_encode($pedido);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Pedido no encontrado']);
        }
    }

    /**
     * Crear un nuevo pedido con sus líneas de detalle
     * Usa transacciones para garantizar que se crea todo o nada.
     * 
     * Parámetros esperados:
     *   - idUsuario: ID del usuario que realiza el pedido
     *   - fecha:     Fecha del pedido
     *   - estado:    Estado inicial ('P' = Pendiente)
     *   - detalles:  JSON con array de líneas [{idProducto, cantidad, precioUnitario}, ...]
     * 
     * @param array $datos Datos del pedido y sus líneas
     */
    public function crearPedido($datos = array()) {
        extract($datos);

        // Validar campos obligatorios
        if (empty($idUsuario) || empty($fecha)) {
            echo '<div class="alert alert-danger">Usuario y fecha son obligatorios</div>';
            return;
        }

        // Decodificar las líneas de detalle del JSON
        $detallesArray = isset($detalles) ? json_decode($detalles, true) : [];
        if (!is_array($detallesArray) || count($detallesArray) == 0) {
            echo '<div class="alert alert-danger">Debe agregar al menos un producto al pedido</div>';
            return;
        }

        // Preparar datos de la cabecera
        $datosPedido = array(
            'idUsuario' => $idUsuario,
            'fecha'     => $fecha,
            'estado'    => isset($estado) ? $estado : 'P'
        );

        // Preparar las líneas del pedido
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

        // Insertar pedido + líneas (transaccional en el modelo)
        $idPedido = $this->modelo->insertarPedido($datosPedido, $lineas);

        echo $idPedido > 0
            ? '<div class="alert alert-success">Pedido creado exitosamente (ID: ' . $idPedido . ')</div>'
            : '<div class="alert alert-danger">Error al crear el pedido</div>';
    }

    /**
     * Actualizar un pedido existente y sus líneas de detalle
     * Borra las líneas antiguas y las recrea (transaccional).
     * 
     * @param array $datos Datos actualizados del pedido incluyendo 'idPedido'
     */
    public function actualizarPedido($datos = array()) {
        extract($datos);

        if (empty($idPedido) || empty($idUsuario) || empty($fecha)) {
            echo '<div class="alert alert-danger">Datos incompletos</div>';
            return;
        }

        // Decodificar las líneas del JSON
        $detallesArray = isset($detalles) ? json_decode($detalles, true) : [];
        if (!is_array($detallesArray) || count($detallesArray) == 0) {
            echo '<div class="alert alert-danger">Debe agregar al menos un producto al pedido</div>';
            return;
        }

        // Preparar datos de la cabecera
        $datosPedido = array(
            'idUsuario' => $idUsuario,
            'fecha'     => $fecha,
            'estado'    => isset($estado) ? $estado : 'P'
        );

        // Preparar las líneas del pedido
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

        // Actualizar pedido + líneas (transaccional en el modelo)
        $resultado = $this->modelo->actualizarPedido($idPedido, $datosPedido, $lineas);

        echo $resultado
            ? '<div class="alert alert-success">Pedido actualizado exitosamente</div>'
            : '<div class="alert alert-danger">Error al actualizar el pedido</div>';
    }

    /**
     * Eliminar un pedido (baja lógica: pone activo='N')
     * 
     * @param array $datos Debe contener 'idPedido'
     */
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

    /**
     * Devolver la lista de usuarios activos en formato JSON
     * Se usa en pedidos.js para llenar el select de usuarios en el formulario.
     */
    public function getUsuariosJSON() {
        $usuarios = $this->modelo->obtenerUsuarios();
        header('Content-Type: application/json');
        echo json_encode($usuarios);
    }

    /**
     * Devolver la lista de productos activos en formato JSON
     * Se usa en pedidos.js para llenar el select de productos en el formulario.
     */
    public function getProductosJSON() {
        $productos = $this->modelo->obtenerProductos();
        header('Content-Type: application/json');
        echo json_encode($productos);
    }
}
?>
