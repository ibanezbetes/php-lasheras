# Proyecto de Gestión de Usuarios y Productos (DI25)

Este proyecto es una aplicación web desarrollada en PHP siguiendo el patrón de diseño **MVC (Modelo-Vista-Controlador)**. Permite la gestión de usuarios y productos, incluyendo funcionalidades de creación, lectura, actualización y eliminación (CRUD).

## 📂 Estructura del Proyecto

El proyecto está organizado en carpetas para separar la lógica, los datos y la interfaz:

- **`/` (Raíz)**: Contiene los archivos principales de entrada (`index.php`, `login.php`) y el controlador frontal (`CFrontal.php`).
- **`controladores/`**: Contiene la lógica de negocio.
  - `CUsuarios.php`: Gestiona las operaciones relacionadas con usuarios.
  - `CProductos.php`: Gestiona las operaciones relacionadas con productos.
  - `Controlador.php`: Clase base para los controladores.
- **`modelos/`**: Contiene la lógica de acceso a datos.
  - `DAO.php`: Clase para conectar con la base de datos MySQL y ejecutar consultas.
- **`vistas/`**: Contiene los archivos HTML/PHP que ve el usuario.
  - `Usuarios/`: Vistas específicas para usuarios (lista, formulario).
  - `Productos/`: Vistas específicas para productos.
  - `Vista.php`: Clase auxiliar para renderizar las vistas.
- **`js/`**: Archivos JavaScript para la interactividad en el cliente (AJAX, validaciones).
- **`css/`**: Estilos de la aplicación.
- **`librerias/`**: Librerías externas como Bootstrap.

## 🚀 Funcionamiento Global

### 1. Inicio de Sesión

El punto de entrada es `login.php`. Aquí el usuario introduce sus credenciales. Si son correctas (usuario: `javier`, contraseña: `123`), se crea una sesión y se redirige a `index.php`.

### 2. Controlador Frontal (`CFrontal.php`)

Todas las peticiones AJAX pasan por este archivo. Actúa como un "semáforo" que dirige el tráfico:

1. Recibe qué **controlador** y qué **método** se quiere ejecutar.
2. Carga el archivo del controlador correspondiente.
3. Ejecuta la acción solicitada.

### 3. Gestión de Usuarios

- **Listar**: Muestra una tabla con los usuarios activos. Permite filtrar por nombre o email.
- **Crear**: Muestra un formulario para dar de alta un nuevo usuario.
- **Editar**: Carga los datos de un usuario existente para modificarlos.
- **Eliminar**: Marca un usuario como inactivo (borrado lógico).

### 4. Gestión de Productos

Similar a usuarios, permite gestionar el inventario de productos, controlando stock y precios.

## 🛠️ Tecnologías Utilizadas

- **PHP**: Lenguaje del servidor.
- **MySQL**: Base de datos.
- **JavaScript (Vanilla)**: Lógica del cliente y peticiones `fetch` (AJAX).
- **Bootstrap 5**: Diseño responsivo y componentes visuales.
- **HTML/CSS**: Estructura y estilos.

## 📝 Notas

- La autenticación actual es básica y está "hardcoded" para demostración.
- Las contraseñas de nuevos usuarios se guardan encriptadas con MD5.
- La aplicación utiliza **AJAX** para que la página no se recargue completamente al navegar entre secciones, ofreciendo una experiencia más fluida.

## 📄 Paginación

Se ha implementado un sistema de paginación **reusable** y genérico que permite navegar por grandes volúmenes de datos de forma eficiente.

### Componentes:

1.  **Vista (`vistas/VPaginador.php`)**: Componente visual que muestra:
    - Total de resultados.
    - Selector de registros por página (5, 10, 20, 50).
    - Botones de navegación (Primera, Anterior, Páginas, Siguiente, Última).
    - Es independiente del modelo de datos.
2.  **JavaScript (`js/paginacion.js`)**: Funciones auxiliares (`cambiarPagina`, `cambiarTamPag`) que manipulan campos ocultos en el formulario de búsqueda y relanzan la petición AJAX.
3.  **Integración (Controladores)**: Los controladores (ej: `CUsuarios.php`) calculan el **OFFSET** y utilizan **SQL LIMIT** para traer solo los datos necesarios de la base de datos, optimizando el rendimiento.
