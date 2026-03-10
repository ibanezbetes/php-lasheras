<?php
/**
 * index.php - Página principal de la aplicación
 * 
 * Es la página de entrada tras el login. Estructura general:
 * 1. Cabecera con logo, título y botón de logout
 * 2. Menú de navegación estático (hardcodeado en HTML)
 * 3. Menú de navegación dinámico (generado desde la BD)
 * 4. Área de contenido dinámico (se carga por AJAX sin recargar la página)
 * 
 * La navegación funciona como una SPA (Single Page Application):
 * al hacer clic en una opción del menú, se carga la vista correspondiente
 * dentro de #capaContenido usando la función obtenerVista() de utils.js.
 * 
 * Seguridad: Requiere sesión activa. Si no hay login, redirige a login.php.
 */
session_start();

// ---------------------------------------------------------------
// Control de acceso: verificar que el usuario está logueado
// ---------------------------------------------------------------
if (isset($_SESSION['login']) && $_SESSION['login'] != '') {
    // Usuario logueado: generar botón de logout con su nombre
    $btnlog = '<div class="d-flex align-items-center justify-content-end">
                   <span class="me-2">' . $_SESSION['login'] . '</span>
                   <a href="logout.php">
                       <img src="iconos/logout.png" style="height:2em;" alt="Logout">
                   </a>
               </div>';
} else {
    // No logueado: redirigir al login
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>App D.I. 2025</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap 5 CSS -->
        <link rel="stylesheet" href="librerias/bootstrap-5.3.8-dist/css/bootstrap.min.css">
        <!-- Estilos personalizados (tema oscuro) -->
        <link rel="stylesheet" href="css/estilos.css">
        <!-- Manifest para PWA -->
        <link rel="manifest" href="manifest.json">
        <!-- Bootstrap 5 JS (incluye Popper para los dropdowns) -->
        <script src="librerias/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <!-- ====== CABECERA: Logo + Título + Logout ====== -->
        <div class="container-fluid py-2">
            <div class="row align-items-center">
                <div class="col-md-2 col-3">
                    <img src="iconos/logo.png" alt="Logo" style="height:48px;">
                </div>
                <div class="col-md-8 col-6 text-center">
                    <h1 class="h4 mb-0">Aplicación de Daniel Ibáñez</h1>
                </div>
                <div class="col-md-2 col-3 text-end">
                    <?php echo $btnlog; ?>
                </div>
            </div>
        </div>

        <!-- ====== MENÚ ESTÁTICO (Eliminado: sustituido por el dinámico) ====== -->

        <!-- ====== MENÚ DINÁMICO (generado desde la base de datos) ====== -->
        <div class="container-fluid">
            <?php
            // Cargar el controlador de menús y generar el menú dinámico
            require_once 'controladores/CMenus.php';
            require_once 'modelos/MMenus.php';
            require_once 'vistas/Vista.php';

            $menuController = new CMenus();
            $menuController->getMenuDinamico();
            ?>
        </div>

        <!-- ====== ZONA DE ALERTAS GENERALES ====== -->
        <div class="container-fluid">
            <div id="appAlert" class="mx-2 mt-3"></div>
        </div>

        <!-- ====== ÁREA DE CONTENIDO DINÁMICO ====== -->
        <!-- Aquí se cargan las vistas (Usuarios, Productos, Pedidos) via AJAX -->
        <div class="container-fluid" id="capaContenido">
            Contenido
        </div>

        <!-- ====== SCRIPTS DE LA APLICACIÓN ====== -->
        <script src="js/utils.js"></script>       <!-- Funciones auxiliares compartidas -->
        <script src="js/usuarios.js"></script>     <!-- Lógica del módulo de usuarios -->
        <script src="js/productos.js"></script>    <!-- Lógica del módulo de productos -->
        <script src="js/paginacion.js"></script>   <!-- Soporte de paginación -->
        <script src="js/pedidos.js"></script>      <!-- Lógica del módulo de pedidos -->
        <script src="pwa.js"></script>             <!-- Registro del Service Worker -->
    </body>
</html>