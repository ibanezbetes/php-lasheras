<!-- ============================================================
     VProductoForm.php - Formulario de crear/editar producto (vista PHP)
     
     NOTA: Este formulario ya NO se usa directamente. La generación
     del formulario se hace ahora desde JavaScript (productos.js)
     mediante las funciones mostrarFormularioCrearProducto() y
     mostrarFormularioEditarProducto().
     
     Se mantiene como referencia de la estructura del formulario.
     
     Recibe (si se usa): $datos['producto'] con los datos del producto a editar
     ============================================================ -->

<?php
// Cargar datos del producto si vienen (modo edición)
$producto = isset($datos['producto']) ? $datos['producto'] : null;
$esEdicion = ($producto !== null);
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?php echo $esEdicion ? 'Editar Producto' : 'Nuevo Producto'; ?></h5>
    </div>
    <div class="card-body">
        <div id="mensajesProducto"></div>
        <form id="formProducto">
            <?php if ($esEdicion): ?>
                <input type="hidden" id="idProducto" value="<?php echo $producto['idProducto']; ?>">
            <?php endif; ?>

            <!-- Nombre del producto -->
            <div class="mb-3">
                <label class="form-label">Producto *</label>
                <input type="text" class="form-control" id="productoNombre" name="producto"
                       value="<?php echo $esEdicion ? $producto['producto'] : ''; ?>" required>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" id="productoDescripcion" name="descripcion" rows="3"><?php echo $esEdicion ? ($producto['descripcion'] ?? '') : ''; ?></textarea>
            </div>

            <!-- Stock -->
            <div class="mb-3">
                <label class="form-label">Stock</label>
                <input type="number" class="form-control" id="productoStock" name="stock"
                       value="<?php echo $esEdicion ? ($producto['stock'] ?? 0) : 0; ?>">
            </div>

            <!-- Precio de venta -->
            <div class="mb-3">
                <label class="form-label">Precio *</label>
                <input type="number" step="0.01" class="form-control" id="productoPrecio" name="precioVenta"
                       value="<?php echo $esEdicion ? $producto['precioVenta'] : ''; ?>" required>
            </div>

            <!-- Botones de acción -->
            <div class="d-grid gap-2">
                <?php if ($esEdicion): ?>
                    <button type="button" class="btn btn-primary" onclick="actualizarProducto();">Actualizar</button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary" onclick="guardarProducto();">Guardar</button>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" onclick="cancelarFormularioProducto();">Cancelar</button>
            </div>
        </form>
    </div>
</div>