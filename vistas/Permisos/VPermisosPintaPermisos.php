<?php  
//parametros que se esperan recibir:  
//  $datos['permisosMenu'][id_Opcion][num_Permiso]=reg permiso
//  $datos['id_Opcion]

require 'VPermisosFunciones.php';  //contiene las funciones que pintas las opciones y permisos
echo pintarPermisos($datos);

?>
