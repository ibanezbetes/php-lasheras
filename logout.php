<?php
/**
 * logout.php - Cierre de sesión
 * 
 * Destruye la sesión del usuario y lo redirige a la página de login.
 * Se llama desde el enlace "Cerrar sesión" en el menú de navegación.
 */
session_start();

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión completamente
session_destroy();

// Redirigir al login
header('Location: login.php');
exit();
?>