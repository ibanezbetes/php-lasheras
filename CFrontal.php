<?php
/**
 * CFrontal.php - Controlador Frontal (Front Controller)
 * 
 * Punto de entrada único para todas las peticiones AJAX de la aplicación.
 * Recibe el nombre del controlador y del método a ejecutar por parámetros,
 * carga dinámicamente la clase correspondiente y ejecuta el método solicitado.
 * 
 * Parámetros esperados (GET o POST):
 *   - controlador: Nombre del módulo (ej: 'Usuarios', 'Productos', 'Pedidos')
 *   - metodo:      Nombre del método a ejecutar (ej: 'getVistaListadoUsuarios')
 * 
 * Ejemplo de URL: CFrontal.php?controlador=Usuarios&metodo=getVistaListadoUsuarios&nombre=Ana
 * 
 * Seguridad: Solo permite el acceso a usuarios logueados (comprueba $_SESSION['login'])
 */
session_start();

// ---------------------------------------------------------------
// Control de acceso: solo usuarios autenticados
// ---------------------------------------------------------------
if (!isset($_SESSION['login']) || $_SESSION['login'] == '') {
    header("Location: login.php");
    exit;
}

// ---------------------------------------------------------------
// Recoger todos los datos de la petición (GET + POST + FILES)
// ---------------------------------------------------------------
$getPost = array_merge($_GET, $_POST, $_FILES);

// ---------------------------------------------------------------
// Enrutamiento: cargar controlador y ejecutar método
// ---------------------------------------------------------------
if (isset($getPost['controlador']) && $getPost['controlador'] != '') {

    // Comprobar si existe el archivo del controlador solicitado
    if (file_exists('controladores/C' . $getPost['controlador'] . '.php')) {

        // Comprobar si se ha indicado qué método ejecutar
        if (isset($getPost['metodo']) && $getPost['metodo'] != '') {

            // Construir el nombre de la clase (Ej: 'Usuarios' → 'CUsuarios')
            $controlador = 'C' . $getPost['controlador'];
            $metodo = $getPost['metodo'];

            // Cargar el archivo del controlador
            require_once 'controladores/' . $controlador . '.php';

            // Crear una instancia del controlador
            $objCont = new $controlador();

            // Ejecutar el método si existe, pasándole todos los datos recibidos
            if (method_exists($objCont, $metodo)) {
                $objCont->$metodo($getPost);
            } else {
                echo 'Error: El método "' . $metodo . '" no existe en ' . $controlador;
            }
        } else {
            echo 'Error: Método no especificado';
        }
    } else {
        echo 'Error: Controlador "' . $getPost['controlador'] . '" no encontrado';
    }
} else {
    echo 'Error: Controlador no especificado';
}
?>
