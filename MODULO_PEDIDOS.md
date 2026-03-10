# 📦 Módulo de Gestión de Pedidos

## Descripción
Sistema completo de gestión de pedidos con líneas de detalle, siguiendo el patrón MVC.

## Características Implementadas ✅

### Backend (PHP)

#### Modelo: `MPedidos.php`
- ✅ Obtener pedidos con filtros (usuario, fecha) y paginación
- ✅ Contar pedidos para paginación
- ✅ Obtener pedido por ID con información del usuario
- ✅ Insertar pedido con líneas de detalle (transaccional)
- ✅ Actualizar pedido completo (cabecera + detalles)
- ✅ Eliminar pedido (con líneas de detalle)
- ✅ Gestión de líneas de pedido
- ✅ Cálculo automático de totales
- ✅ Obtener usuarios activos
- ✅ Obtener productos activos

#### Controlador: `CPedidos.php`
- ✅ Vista principal de pedidos
- ✅ Listado de pedidos con filtros y paginación
- ✅ Obtener pedido por ID (JSON)
- ✅ Crear pedido con validaciones
- ✅ Actualizar pedido completo
- ✅ Eliminar pedido
- ✅ Obtener usuarios activos (JSON)
- ✅ Obtener productos activos (JSON)

### Frontend (JavaScript)

#### Funcionalidades: `pedidos.js`
- ✅ Búsqueda de pedidos con filtros
- ✅ Ver todos los pedidos
- ✅ Formulario modal para crear/editar pedidos
- ✅ Gestión dinámica de líneas de detalle
- ✅ Añadir productos con cantidad
- ✅ Eliminar líneas de detalle
- ✅ Cálculo automático de totales
- ✅ Validaciones en cliente
- ✅ Editar pedido existente
- ✅ Eliminar pedido con confirmación
- ✅ Detección de productos duplicados (suma cantidades)

### Base de Datos

#### Tablas
- `pedidos`: Cabecera del pedido (idPedido, idUsuario, fecha, total, estado)
- `pedidos_detalles`: Líneas del pedido (idDetalle, idPedido, idProducto, cantidad, precioUnitario)

#### Estados de Pedido
- `P`: Pendiente ⏳
- `C`: Completado ✅

## Uso

### Crear un Pedido
1. Click en "Crear Nuevo Pedido"
2. Seleccionar usuario y fecha
3. Añadir productos con cantidad
4. Guardar

### Editar un Pedido
1. Click en el botón ✏️ del pedido
2. Modificar datos necesarios
3. Añadir/eliminar líneas de detalle
4. Guardar cambios

### Eliminar un Pedido
1. Click en el botón ❌ del pedido
2. Confirmar eliminación

### Buscar Pedidos
- Por nombre de usuario
- Por fecha específica
- Ver todos los pedidos

## Características Técnicas

### Transacciones
- Las operaciones de inserción y actualización usan transacciones para garantizar integridad
- Si falla alguna línea de detalle, se revierte toda la operación

### Validaciones
- Usuario y fecha obligatorios
- Al menos un producto en el pedido
- Cantidades mayores a 0
- Precios válidos

### Seguridad
- Uso de prepared statements en el modelo
- Validaciones en backend y frontend
- Manejo de errores consistente

### UX/UI
- Formulario modal responsive
- Diseño con Bootstrap 5
- Iconos para mejor visualización
- Mensajes de éxito/error claros
- Confirmaciones para acciones destructivas

## Archivos del Módulo

```
modelos/
  └── MPedidos.php          # Modelo de datos

controladores/
  └── CPedidos.php          # Lógica de negocio

vistas/
  └── Pedidos/
      └── VPedidosPrincipal.php  # Vista principal

js/
  └── pedidos.js            # Lógica frontend

sql/
  └── crear_menus_y_pedidos.sql  # Script de BD
```

## Próximas Mejoras Posibles

- [ ] Exportar pedidos a PDF
- [ ] Filtros avanzados (rango de fechas, estado)
- [ ] Estadísticas de pedidos
- [ ] Historial de cambios
- [ ] Notificaciones por email
- [ ] Integración con inventario
