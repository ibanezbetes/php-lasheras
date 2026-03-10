<?php
/**
 * CProductos.php - Controlador del módulo de Productos
 * 
 * Gestiona todas las operaciones del módulo de productos:
 * - Mostrar la vista principal
 * - Listar productos con búsqueda y paginación
 * - Crear, editar y eliminar productos
 * 
 * Sigue el mismo patrón MVC que CUsuarios.
 */
require_once 'controladores/Controlador.php';
require_once 'vistas/Vista.php';
require_once 'modelos/DAO.php';

class CProductos extends Controlador {
    /** @var DAO Objeto de acceso a datos */
    private $dao;

    /**
     * Constructor - Inicializa la conexión a la base de datos
     */
    public function __construct() {
        $this->dao = new DAO();
    }

    /**
     * Cargar la vista principal del módulo de productos
     * 
     * @param array $datos Parámetros recibidos (no se usan)
     */
    public function getVistaProductosPrincipal($datos = array()) {
        Vista::render('vistas/Productos/VProductosPrincipal.php');
    }

    /**
     * Buscar y listar productos con filtros y paginación
     * Se llama por AJAX desde productos.js → buscarProductos()
     * 
     * Parámetros esperados en $datos:
     *   - producto: Filtro por nombre de producto (opcional)
     *   - pagina:   Página actual (por defecto 1)
     *   - tam_pag:  Registros por página (por defecto 15)
     * 
     * @param array $datos Parámetros de búsqueda y paginación
     */
    public function getVistaListadoProductos($datos = array()) {
        extract($datos);
        $producto = isset($producto) ? $producto : '';
        $pagina   = isset($pagina)   ? (int)$pagina : 1;
        $tamPag   = isset($tam_pag)  ? (int)$tam_pag : 15;

        // ----- 1. Contar el TOTAL de registros -----
        $sqlCount = "SELECT COUNT(*) as total FROM productos WHERE activo='S'";
        $filtro = "";
        if ($producto != '') $filtro .= " AND producto LIKE '%$producto%'";
        $sqlCount .= $filtro;

        $res = $this->dao->consultar($sqlCount);
        $totalRegistros = $res[0]['total'];

        // ----- 2. Obtener solo los registros de la página actual -----
        $offset = ($pagina - 1) * $tamPag;

        $sql = "SELECT idProducto, producto, descripcion, stock, precioVenta 
                FROM productos WHERE activo='S'";
        $sql .= $filtro;
        $sql .= " ORDER BY producto";
        $sql .= " LIMIT $offset, $tamPag";

        $productos = $this->dao->consultar($sql);

        // ----- 3. Generar la tabla HTML -----
        if (count($productos) > 0) {
            echo '<div class="table-responsive"><table class="table table-striped table-hover">
                  <thead><tr>
                      <th>ID</th><th>Producto</th><th>Descripción</th>
                      <th>Stock</th><th>Precio</th><th>Acciones</th>
                  </tr></thead><tbody>';

            foreach ($productos as $p) {
                echo '<tr>
                        <td>' . $p['idProducto'] . '</td>
                        <td>' . $p['producto'] . '</td>
                        <td>' . $p['descripcion'] . '</td>
                        <td>' . $p['stock'] . '</td>
                        <td>' . $p['precioVenta'] . ' €</td>
                        <td>
                            <button class="btn btn-sm btn-primary me-1" 
                                    onclick="editarProducto(' . $p['idProducto'] . ');">✏️</button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="eliminarProducto(' . $p['idProducto'] . ',\'' . addslashes($p['producto']) . '\');">❌</button>
                        </td>
                      </tr>';
            }
            echo '</tbody></table></div>';

            // ----- 4. Renderizar la paginación -----
            Vista::render('vistas/VPaginacion.php', array(
                'totalRegistros'  => $totalRegistros,
                'pagActual'       => $pagina,
                'tamPag'          => $tamPag,
                'funcionCallback' => 'buscarProductos'
            ));
        } else {
            echo '<div class="alert alert-warning">No se encontraron productos</div>';
        }
    }

    /**
     * Obtener los datos de un producto concreto (formato JSON, para edición)
     * 
     * @param array $datos Debe contener 'idProducto'
     */
    public function obtenerProducto($datos = array()) {
        extract($datos);
        $sql = "SELECT * FROM productos WHERE idProducto = $idProducto";
        $productos = $this->dao->consultar($sql);
        header('Content-Type: application/json');
        echo json_encode(count($productos) > 0 ? $productos[0] : ['error' => 'No encontrado']);
    }

    /**
     * Crear un nuevo producto en la base de datos
     * 
     * @param array $datos Campos: producto, descripcion, stock, precioVenta
     */
    public function crearProducto($datos = array()) {
        extract($datos);

        if (empty($producto) || !isset($precioVenta)) {
            echo '<div class="alert alert-danger">Nombre y precio son obligatorios</div>';
            return;
        }

        $sql = "INSERT INTO productos (producto, descripcion, stock, precioVenta, activo) 
                VALUES ('$producto','$descripcion','$stock','$precioVenta','S')";
        $id = $this->dao->insertar($sql);
        echo $id > 0
            ? '<div class="alert alert-success">Producto creado exitosamente</div>'
            : '<div class="alert alert-danger">Error al crear el producto</div>';
    }

    /**
     * Actualizar los datos de un producto existente
     * 
     * @param array $datos Campos del formulario incluyendo 'idProducto'
     */
    public function actualizarProducto($datos = array()) {
        extract($datos);

        if (empty($idProducto) || empty($producto) || !isset($precioVenta)) {
            echo '<div class="alert alert-danger">Campos obligatorios incompletos</div>';
            return;
        }

        $sql = "UPDATE productos SET producto='$producto', descripcion='$descripcion', 
                stock='$stock', precioVenta='$precioVenta' 
                WHERE idProducto=$idProducto";
        $res = $this->dao->actualizar($sql);
        echo $res >= 0
            ? '<div class="alert alert-success">Producto actualizado exitosamente</div>'
            : '<div class="alert alert-danger">Error al actualizar el producto</div>';
    }

    /**
     * Eliminar un producto (baja lógica: pone activo='N')
     * 
     * @param array $datos Debe contener 'idProducto'
     */
    public function eliminarProducto($datos = array()) {
        extract($datos);

        $sql = "UPDATE productos SET activo='N' WHERE idProducto=$idProducto";
        $res = $this->dao->actualizar($sql);
        echo $res > 0
            ? '<div class="alert alert-success">Producto eliminado exitosamente</div>'
            : '<div class="alert alert-danger">Error al eliminar el producto</div>';
    }
}
?>
