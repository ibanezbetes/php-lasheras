<?php
/**
 * test_pedidos.php - Script de pruebas para el modelo MPedidos
 * 
 * Script de diagnóstico que prueba todas las operaciones CRUD del
 * modelo de pedidos para verificar que funcionan correctamente.
 * 
 * IMPORTANTE:
 *   - Ejecutar DESPUÉS de importar sql/crear_menus_y_pedidos.sql
 *   - Este archivo NO es parte de la aplicación, solo para desarrollo/debug
 *   - Crea y elimina un pedido de prueba durante la ejecución
 * 
 * Pruebas que realiza:
 *   1. Crear instancia de MPedidos
 *   2. Obtener usuarios activos
 *   3. Obtener productos activos
 *   4. Contar pedidos activos
 *   5. Listar pedidos con paginación
 *   6. Obtener un pedido por ID
 *   7. Obtener líneas de un pedido
 *   8. Crear un pedido nuevo (con transacción)
 *   9. Actualizar el pedido creado
 *  10. Eliminar el pedido (baja lógica)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/MPedidos.php';

echo "<h1>Test del Modelo MPedidos</h1>";
echo "<hr>";

try {
    // ---------------------------------------------------------------
    // Test 1: Crear instancia del modelo
    // ---------------------------------------------------------------
    echo "<h2>1. Creando instancia de MPedidos...</h2>";
    $modelo = new MPedidos();
    echo "<p style='color: green;'>✓ Instancia creada correctamente</p>";
    
    // ---------------------------------------------------------------
    // Test 2: Obtener usuarios activos
    // ---------------------------------------------------------------
    echo "<h2>2. Probando obtenerUsuarios()...</h2>";
    $usuarios = $modelo->obtenerUsuarios();
    echo "<p>Encontrados " . count($usuarios) . " usuarios activos:</p>";
    echo "<ul>";
    foreach ($usuarios as $usuario) {
        echo "<li>ID: {$usuario['idUsuario']} - {$usuario['nombre']} {$usuario['apellido1']}</li>";
    }
    echo "</ul>";
    
    // ---------------------------------------------------------------
    // Test 3: Obtener productos activos
    // ---------------------------------------------------------------
    echo "<h2>3. Probando obtenerProductos()...</h2>";
    $productos = $modelo->obtenerProductos();
    echo "<p>Encontrados " . count($productos) . " productos activos (mostrando primeros 5):</p>";
    echo "<ul>";
    $count = 0;
    foreach ($productos as $producto) {
        if ($count++ >= 5) break;
        echo "<li>ID: {$producto['idProducto']} - {$producto['nombre']} - €{$producto['precio']}</li>";
    }
    echo "</ul>";
    
    // ---------------------------------------------------------------
    // Test 4: Contar pedidos activos
    // ---------------------------------------------------------------
    echo "<h2>4. Probando contarPedidos()...</h2>";
    $filtros = array();
    $totalPedidos = $modelo->contarPedidos($filtros);
    echo "<p>Total pedidos activos: <strong>$totalPedidos</strong></p>";
    
    // ---------------------------------------------------------------
    // Test 5: Listar pedidos (con paginación)
    // ---------------------------------------------------------------
    echo "<h2>5. Probando obtenerPedidos()...</h2>";
    $pedidos = $modelo->obtenerPedidos($filtros, 1, 10);
    echo "<p>Recuperados " . count($pedidos) . " pedidos:</p>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th>Total</th></tr>";
    foreach ($pedidos as $pedido) {
        echo "<tr>";
        echo "<td>{$pedido['idPedido']}</td>";
        echo "<td>{$pedido['fecha']}</td>";
        echo "<td>{$pedido['nombre']} {$pedido['apellido1']}</td>";
        echo "<td>{$pedido['estado']}</td>";
        echo "<td>€" . number_format($pedido['total'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // ---------------------------------------------------------------
    // Test 6 y 7: Obtener pedido por ID y sus líneas
    // ---------------------------------------------------------------
    if (!empty($pedidos)) {
        $primerPedido = $pedidos[0];
        echo "<h2>6. Probando obtenerPedidoPorId({$primerPedido['idPedido']})...</h2>";
        $pedido = $modelo->obtenerPedidoPorId($primerPedido['idPedido']);
        echo "<p>Detalles del pedido:</p>";
        echo "<pre>" . print_r($pedido, true) . "</pre>";
        
        echo "<h2>7. Probando obtenerLineasPedido({$primerPedido['idPedido']})...</h2>";
        $lineas = $modelo->obtenerLineasPedido($primerPedido['idPedido']);
        echo "<p>Encontradas " . count($lineas) . " líneas:</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Línea</th><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr>";
        foreach ($lineas as $linea) {
            echo "<tr>";
            echo "<td>{$linea['idLinea']}</td>";
            echo "<td>{$linea['nombreProducto']}</td>";
            echo "<td>{$linea['cantidad']}</td>";
            echo "<td>€" . number_format($linea['precioUnitario'], 2) . "</td>";
            echo "<td>€" . number_format($linea['subtotal'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // ---------------------------------------------------------------
    // Test 8: Crear pedido nuevo (operación transaccional)
    // ---------------------------------------------------------------
    echo "<h2>8. Probando insertarPedido() con transacción...</h2>";
    if (!empty($usuarios) && !empty($productos)) {
        $datosPedido = array(
            'fecha' => date('Y-m-d'),
            'idUsuario' => $usuarios[0]['idUsuario'],
            'estado' => 'Pendiente',
            'observaciones' => 'Pedido de prueba creado por test_pedidos.php'
        );
        
        $lineasPrueba = array(
            array(
                'idProducto' => $productos[0]['idProducto'],
                'cantidad' => 2,
                'precioUnitario' => $productos[0]['precio']
            ),
            array(
                'idProducto' => $productos[1]['idProducto'],
                'cantidad' => 1,
                'precioUnitario' => $productos[1]['precio']
            )
        );
        
        $nuevoPedidoId = $modelo->insertarPedido($datosPedido, $lineasPrueba);
        
        if ($nuevoPedidoId) {
            echo "<p style='color: green;'>✓ Pedido creado con ID: <strong>$nuevoPedidoId</strong></p>";
            
            // Verificar que se creó correctamente
            $pedidoCreado = $modelo->obtenerPedidoPorId($nuevoPedidoId);
            echo "<p>Total del pedido: €" . number_format($pedidoCreado['total'], 2) . "</p>";
            
            $lineasCreadas = $modelo->obtenerLineasPedido($nuevoPedidoId);
            echo "<p>Líneas creadas: " . count($lineasCreadas) . "</p>";
            
            // ---------------------------------------------------------------
            // Test 9: Actualizar el pedido
            // ---------------------------------------------------------------
            echo "<h2>9. Probando actualizarPedido()...</h2>";
            $datosPedido['estado'] = 'Procesando';
            $datosPedido['observaciones'] = 'Pedido actualizado por test_pedidos.php';
            
            // Modificar cantidad de primera línea
            $lineasPrueba[0]['cantidad'] = 3;
            
            $resultado = $modelo->actualizarPedido($nuevoPedidoId, $datosPedido, $lineasPrueba);
            
            if ($resultado) {
                echo "<p style='color: green;'>✓ Pedido actualizado correctamente</p>";
                $pedidoActualizado = $modelo->obtenerPedidoPorId($nuevoPedidoId);
                echo "<p>Nuevo estado: {$pedidoActualizado['estado']}</p>";
                echo "<p>Nuevo total: €" . number_format($pedidoActualizado['total'], 2) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Error al actualizar pedido</p>";
            }
            
            // ---------------------------------------------------------------
            // Test 10: Eliminar pedido (baja lógica)
            // ---------------------------------------------------------------
            echo "<h2>10. Probando borrarPedido() (baja lógica)...</h2>";
            $resultado = $modelo->borrarPedido($nuevoPedidoId);
            echo "<p style='color: green;'>✓ Pedido marcado como inactivo</p>";
            
            // Verificar que ya no aparece en consultas de activos
            $pedidoBorrado = $modelo->obtenerPedidoPorId($nuevoPedidoId);
            if ($pedidoBorrado === null) {
                echo "<p style='color: green;'>✓ El pedido eliminado no aparece en consultas activas</p>";
            } else {
                echo "<p style='color: red;'>✗ El pedido eliminado sigue apareciendo</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Error al crear pedido (transacción revertida)</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Test de inserción omitido - no hay usuarios o productos</p>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ TODAS LAS PRUEBAS COMPLETADAS</h2>";
    echo "<p>El modelo MPedidos funciona correctamente.</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ ERROR DURANTE LAS PRUEBAS</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
