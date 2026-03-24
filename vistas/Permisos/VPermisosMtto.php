<?php  
//parametros que se esperan recibir:
$menu=array(); //Todas las opciones del menu
$permisosMenu=array(); // todos los permisos de las opciones
$permisosRol=array(); //todos los permisos del rol seleccionado.
$permisosUsuario=array(); //todos los permisos del usuario seleccionado.
$permisosUsuarioPorRoles=array(); //$per[id_permiso][id_Rol]=id_Rol //todos los permisos del usuario seleccionado.
$rolesUsuario=array();// $rolesUsuario[id_Rol]=regRol //roles del usuario
$idRol='';
$usuario='';
$ver='';
$datosRol=array();
extract($datos);

$html='';
$idCapa=1;

$datos['id_Padre']=0;
//$datos['ver']='Menu';
require 'VPermisosFunciones.php';  //contiene las funciones que pintas las opciones y permisos

if($datos['ver']=='Menu'){
    echo '<span class="titulo-permisos">Configuración del Menú Base</span>';
}
if($datos['ver']=='Rol'){
    echo '<span class="titulo-permisos">Permisos asignados al Rol: <b>'.$datosRol[$idRol]['rol'].'</b></span>';
}
if($datos['ver']=='Usuario'){
    echo '<span class="titulo-permisos">Permisos asignados al Usuario: <b>'.$usuario.'</b></span>';
}

//cominzar a pintar las opciones
if($ver=='Usuario' && !empty($rolesUsuario)){ // mostrar roles del usuario
    echo '<div class="roles-resaltados">
            <span style="font-weight:bold">Roles heredados por el usuario:</span><br>';
    foreach($rolesUsuario as $rol){
        echo '&nbsp;&nbsp;&nbsp;'.$rol['rol'].'<br>';
    }
    echo '</div><br>';
}
echo pintarSubOpciones($datos);

?>
