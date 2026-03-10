-- ============================================================
-- reemplazar_productos_informatica.sql
-- Base de datos: db_di25
--
-- Script para reemplazar los antiguos productos por 20 
-- componentes de informática y periféricos.
--
-- ⚠️ ADVERTENCIA: Este script vaciará las tablas de pedidos y 
-- líneas de detalle (por las claves foráneas) antes de 
-- sustituir los productos.
-- ============================================================

USE `db_di25`;

-- 1. Desactivar revisión de Foreign Keys de forma temporal
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Vaciar las tablas dependientes y la de productos
DELETE FROM `lineas_pedido`;
DELETE FROM `pedidos_detalles`;
DELETE FROM `pedidos`;
DELETE FROM `productos`;

-- 3. Volver a activar la revisión de Foreign Keys
SET FOREIGN_KEY_CHECKS = 1;

-- 4. Insertar los 20 nuevos productos de informática
INSERT INTO `productos` (`idProducto`, `producto`, `descripcion`, `stock`, `precioVenta`, `activo`) VALUES
(1, 'Procesador AMD Ryzen 7 7800X3D', 'Procesador AM5 de 8 núcleos, perfecto para gaming', 15, 389.90, 'S'),
(2, 'Procesador Intel Core i5-13600K', 'Procesador LGA1700 de 14 núcleos (6P+8E)', 20, 319.50, 'S'),
(3, 'Placa Base ASUS ROG Strix B650E-F', 'Placa base ATX AM5 con WiFi 6E y PCIe 5.0', 10, 275.99, 'S'),
(4, 'Placa Base MSI MAG B760 TOMAHAWK', 'Placa base ATX LGA1700, DDR5, WiFi 6', 12, 199.90, 'S'),
(5, 'Memoria RAM Corsair Vengeance RGB 32GB', 'Kit DDR5 (2x16GB) 6000MHz CL30', 25, 134.99, 'S'),
(6, 'Memoria RAM Kingston FURY Beast 16GB', 'Módulo DDR4 3200MHz CL16', 40, 45.99, 'S'),
(7, 'Tarjeta Gráfica NVIDIA RTX 4070 SUPER', 'GPU de 12GB GDDR6X, DLSS 3, 3 ventiladores', 8, 659.90, 'S'),
(8, 'Tarjeta Gráfica AMD Radeon RX 7800 XT', 'GPU de 16GB GDDR6, ideal para 1440p', 10, 539.90, 'S'),
(9, 'Disco SSD M.2 Samsung 990 PRO 2TB', 'SSD NVMe PCIe 4.0 con velocidades hasta 7450 MB/s', 30, 189.99, 'S'),
(10, 'Disco SSD M.2 WD Blue SN580 1TB', 'SSD NVMe PCIe 4.0 económico y rápido', 45, 69.90, 'S'),
(11, 'Fuente de Alimentación Corsair RM850x', 'Fuente de 850W, 80 Plus Gold, Modular', 15, 149.90, 'S'),
(12, 'Refrigeración Líquida MSI MAG CoreLiquid 360R', 'AIO de 360mm con RGB, socket universal', 18, 115.50, 'S'),
(13, 'Disipador Noctua NH-D15 chromax.black', 'Disipador de aire premium de doble torre', 14, 119.90, 'S'),
(14, 'Caja Phanteks Eclipse G360A Mid-Tower', 'Caja ATX con frontal mallado y 3 ventiladores D-RGB', 20, 99.90, 'S'),
(15, 'Monitor LG UltraGear 27GP850-B', 'Monitor Gaming 27p Nano IPS, 1440p, 165Hz, 1ms', 12, 329.00, 'S'),
(16, 'Ratón Logitech G Pro X Superlight', 'Ratón inalámbrico ultraligero para eSports', 22, 125.99, 'S'),
(17, 'Ratón Razer DeathAdder V3 Pro', 'Ratón ergonómico inalámbrico, sensor óptico de 30K', 15, 145.50, 'S'),
(18, 'Teclado Mecánico Corsair K70 RGB PRO', 'Teclado completo con switches Cherry MX Red', 10, 169.90, 'S'),
(19, 'Teclado Mecánico Keychron K8 Pro', 'Teclado custom TKL, Bluetooth/Cable, Hot-Swappable', 18, 119.00, 'S'),
(20, 'Auriculares HyperX Cloud III Wireless', 'Auriculares gaming inalámbricos con autonomía de 120h', 25, 139.99, 'S');

-- 5. Crear unos pedidos de prueba con los nuevos productos
INSERT INTO `pedidos` (`fecha`, `idUsuario`, `estado`, `total`, `observaciones`) VALUES
('2025-03-01', 1, 'Completado', 1184.79, 'PC completo - Parte 1'),
('2025-03-05', 2, 'Pendiente', 329.00, 'Revisar si el monitor tiene pixeles muertos'),
('2025-03-10', 1, 'Pendiente', 265.98, 'Periféricos para la oficina');

INSERT INTO `lineas_pedido` (`idPedido`, `idProducto`, `cantidad`, `precioUnitario`, `subtotal`) VALUES
(1, 1, 1, 389.90, 389.90),
(1, 5, 1, 134.99, 134.99),
(1, 7, 1, 659.90, 659.90),
(2, 15, 1, 329.00, 329.00),
(3, 16, 1, 125.99, 125.99),
(3, 20, 1, 139.99, 139.99);
