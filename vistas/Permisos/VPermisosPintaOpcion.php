<?php  
//parametros que se esperan recibir:
$menu=array(); //Todas las opciones del menu (solo uno)
//extract($datos);

//echo json_encode(($datos));

//$datos['id_Padre']=0;
require 'VPermisosFunciones.php';  //contiene las funciones que pintas las opciones y permisos
echo pintarSubOpciones($datos);

?>
