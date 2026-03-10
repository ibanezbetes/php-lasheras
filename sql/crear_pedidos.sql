-- ============================================================
-- crear_pedidos.sql
-- Base de datos: db_di25
--
-- Script para crear SOLO las tablas del módulo de pedidos.
-- Usar este script si el menú dinámico ya existe y solo necesitas
-- crear/recrear las tablas de pedidos y líneas.
--
-- IMPORTANTE: Ejecutar DESPUÉS de importar las tablas de
-- usuarios y productos (tienen claves foráneas).
--
-- Para crear TODO (menús + pedidos), usar: crear_menus_y_pedidos.sql
-- Para corregir una BD existente sin perder datos: fix_pedidos.sql
-- ============================================================

USE `db_di25`;

-- Borrar tablas existentes (primero la hija, luego la padre)
DROP TABLE IF EXISTS `lineas_pedido`;
DROP TABLE IF EXISTS `pedidos`;

-- ============================================================
-- Tabla: pedidos (Cabecera del pedido - MAESTRO)
-- ============================================================
-- Campos:
--   idPedido:      ID autoincremental
--   fecha:         Fecha del pedido
--   idUsuario:     Usuario que realiza el pedido (FK → usuarios)
--   estado:        'P' = Pendiente, 'C' = Completado
--   total:         Suma de los subtotales de las líneas
--   observaciones: Notas opcionales
--   activo:        'S' = Activo, 'N' = Eliminado (baja lógica)
--   fechaCreacion: Fecha de creación automática
CREATE TABLE `pedidos` (
  `idPedido` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha` DATE NOT NULL,
  `idUsuario` INT(11) UNSIGNED NOT NULL,
  `estado` VARCHAR(20) COLLATE latin1_spanish_ci NOT NULL DEFAULT 'P',
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` TEXT COLLATE latin1_spanish_ci NULL DEFAULT NULL,
  `activo` CHAR(1) COLLATE latin1_spanish_ci NOT NULL DEFAULT 'S',
  `fechaCreacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idPedido`),
  INDEX `idx_fecha` (`fecha`),
  INDEX `idx_usuario` (`idUsuario`),
  INDEX `idx_activo` (`activo`),
  CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- ============================================================
-- Tabla: lineas_pedido (Líneas de detalle - DETALLE)
-- ============================================================
-- Campos:
--   idLinea:         ID autoincremental de la línea
--   idPedido:        Pedido al que pertenece (FK → pedidos, CASCADE)
--   idProducto:      Producto de esta línea (FK → productos)
--   cantidad:        Unidades pedidas
--   precioUnitario:  Precio por unidad en el momento del pedido
--   subtotal:        cantidad * precioUnitario
CREATE TABLE `lineas_pedido` (
  `idLinea` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idPedido` INT(11) UNSIGNED NOT NULL,
  `idProducto` INT(11) NOT NULL,
  `cantidad` INT NOT NULL,
  `precioUnitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idLinea`),
  INDEX `idx_pedido` (`idPedido`),
  CONSTRAINT `fk_lineas_pedido` FOREIGN KEY (`idPedido`) REFERENCES `pedidos` (`idPedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_lineas_producto` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`idProducto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- ============================================================
-- Datos de prueba
-- ============================================================

-- Pedidos de ejemplo (estados: P = Pendiente, C = Completado)
INSERT INTO `pedidos` (`fecha`, `idUsuario`, `estado`, `total`, `observaciones`) VALUES
('2025-01-15', 1, 'C', 150.00, 'Pedido urgente - entrega prioritaria'),
('2025-01-16', 2, 'P', 75.51, 'Cliente solicita confirmación antes de envío'),
('2025-01-20', 1, 'P', 320.40, NULL),
('2025-02-01', 3, 'P', 95.70, 'Verificar disponibilidad de stock'),
('2025-02-05', 2, 'C', 214.30, NULL);

-- Líneas de detalle de cada pedido
INSERT INTO `lineas_pedido` (`idPedido`, `idProducto`, `cantidad`, `precioUnitario`, `subtotal`) VALUES
-- Pedido 1: 2 líneas
(1, 1, 2, 50.00, 100.00),
(1, 2, 1, 50.00, 50.00),
-- Pedido 2: 1 línea
(2, 3, 3, 25.17, 75.51),
-- Pedido 3: 3 líneas
(3, 1, 1, 95.70, 95.70),
(3, 2, 1, 214.30, 214.30),
(3, 5, 1, 10.40, 10.40),
-- Pedido 4: 1 línea
(4, 1, 1, 95.70, 95.70),
-- Pedido 5: 1 línea
(5, 2, 1, 214.30, 214.30);

-- ============================================================
-- Verificación
-- ============================================================
SELECT 'Tablas de pedidos creadas correctamente' AS Estado;
SELECT COUNT(*) AS TotalPedidos FROM pedidos;
SELECT COUNT(*) AS TotalLineas FROM lineas_pedido;
