<?php
/**
 * Script para unificar toda la base de datos en un solo archivo SQL.
 * Almacenará el resultado en bd_completa_ec2.sql
 */

$archivos_sql = [
    'sql-new/usuarios y productos 2025 09 29.sql',
    'sql/crear_menus_y_pedidos.sql',
    'sql/reemplazar_productos_informatica.sql',
    'sql/permisos_setup.sql'
];

$contenido_final = "-- ========================================================\n";
$contenido_final .= "-- BASE DE DATOS COMPLETA PARA DESPLIEGUE EN EC2 (AWS)\n";
$contenido_final .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n";
$contenido_final .= "-- ========================================================\n\n";

$contenido_final .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($archivos_sql as $archivo) {
    if (file_exists($archivo)) {
        $contenido_final .= "-- --------------------------------------------------------\n";
        $contenido_final .= "-- INCLUYENDO: $archivo\n";
        $contenido_final .= "-- --------------------------------------------------------\n\n";
        
        $sql = file_get_contents($archivo);
        
        // Evitar que vuelvan a crear la base de datos o usen un 'USE' si no queremos
        // pero en este caso está bien dejarlo.
        $contenido_final .= $sql . "\n\n";
    } else {
        echo "<h3>No se pudo encontrar: $archivo</h3>";
    }
}

$contenido_final .= "SET FOREIGN_KEY_CHECKS = 1;\n";

$nombre_salida = 'bd_completa_ec2.sql';
$bytes = file_put_contents($nombre_salida, $contenido_final);

if ($bytes) {
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: green;'>¡Archivo SQL unificado creado con éxito!</h2>";
    echo "<p>Se ha generado el archivo <b>$nombre_salida</b> (" . round($bytes / 1024, 2) . " KB).</p>";
    echo "<p>Ruta: " . realpath($nombre_salida) . "</p>";
    echo "<p>Ya puedes coger este archivo, subirlo a tu instancia EC2 e importarlo desde phpMyAdmin.</p>";
    echo "</div>";
} else {
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: red;'>Error al generar el archivo</h2>";
    echo "<p>Verifica los permisos de escritura en el directorio del proyecto.</p>";
    echo "</div>";
}
?>
