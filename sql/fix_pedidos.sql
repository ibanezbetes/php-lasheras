-- ============================================================
-- fix_pedidos.sql
-- Base de datos: db_di25
--
-- Script de REPARACIÓN para la tabla de pedidos.
-- Usar cuando la BD ya existe pero le faltan columnas o la
-- tabla de líneas tiene un nombre antiguo (pedidos_detalles).
--
-- Errores comunes que soluciona:
--   - "Unknown column 'p.activo' in 'where clause'"
--   - "Unknown column 'observaciones'"
--   - "Table 'lineas_pedido' doesn't exist" (si existe pedidos_detalles)
--
-- Este script es SEGURO: comprueba si las columnas/tablas ya
-- existen antes de hacer cambios (idempotente).
-- ============================================================

USE `db_di25`;

-- ============================================================
-- PASO 1: Añadir columna 'activo' a pedidos (si falta)
-- Usada para baja lógica: 'S' = activo, 'N' = eliminado
-- ============================================================
SET @column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'db_di25' AND TABLE_NAME = 'pedidos' AND COLUMN_NAME = 'activo'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE pedidos ADD COLUMN activo CHAR(1) NOT NULL DEFAULT \'S\' AFTER total',
    'SELECT \'La columna activo ya existe\' AS Estado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- PASO 2: Añadir columna 'observaciones' (si falta)
-- ============================================================
SET @col2 = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'db_di25' AND TABLE_NAME = 'pedidos' AND COLUMN_NAME = 'observaciones'
);

SET @sql2 = IF(@col2 = 0, 
    'ALTER TABLE pedidos ADD COLUMN observaciones TEXT NULL DEFAULT NULL AFTER activo',
    'SELECT \'La columna observaciones ya existe\' AS Estado');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- ============================================================
-- PASO 3: Añadir columna 'fechaCreacion' (si falta)
-- ============================================================
SET @col3 = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'db_di25' AND TABLE_NAME = 'pedidos' AND COLUMN_NAME = 'fechaCreacion'
);

SET @sql3 = IF(@col3 = 0, 
    'ALTER TABLE pedidos ADD COLUMN fechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER observaciones',
    'SELECT \'La columna fechaCreacion ya existe\' AS Estado');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- ============================================================
-- PASO 4: Migrar tabla 'pedidos_detalles' → 'lineas_pedido'
-- Si existe la tabla antigua, copiar sus datos a la nueva.
-- ============================================================
SET @lp_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'db_di25' AND TABLE_NAME = 'lineas_pedido'
);

SET @pd_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'db_di25' AND TABLE_NAME = 'pedidos_detalles'
);

-- Si lineas_pedido no existe pero pedidos_detalles sí, crear lineas_pedido con los datos
SET @create_lp = IF(@lp_exists = 0 AND @pd_exists = 1,
    'CREATE TABLE lineas_pedido AS SELECT idDetalle AS idLinea, idPedido, idProducto, cantidad, precioUnitario, (cantidad * precioUnitario) AS subtotal FROM pedidos_detalles',
    'SELECT \'Tabla lineas_pedido OK\' AS Estado');
PREPARE stmt4 FROM @create_lp;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

-- Si ninguna de las dos tablas existe, crear lineas_pedido desde cero
SET @lp_exists2 = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'db_di25' AND TABLE_NAME = 'lineas_pedido'
);

SET @create_fresh = IF(@lp_exists2 = 0,
    'CREATE TABLE lineas_pedido (
        idLinea INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        idPedido INT(11) UNSIGNED NOT NULL,
        idProducto INT(11) NOT NULL,
        cantidad INT NOT NULL,
        precioUnitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (idLinea),
        INDEX idx_pedido (idPedido)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1',
    'SELECT \'Tabla lineas_pedido ya existe\' AS Estado');
PREPARE stmt5 FROM @create_fresh;
EXECUTE stmt5;
DEALLOCATE PREPARE stmt5;

-- ============================================================
-- PASO 5: Asegurar que todos los pedidos estén marcados como activos
-- (por si la columna se añadió después de insertar datos)
-- ============================================================
UPDATE pedidos SET activo = 'S' WHERE activo IS NULL OR activo = '';

-- ============================================================
-- Verificación final
-- ============================================================
SELECT 'Reparación completada correctamente' AS Estado;
SELECT COUNT(*) AS TotalPedidos FROM pedidos;
SHOW COLUMNS FROM pedidos;
