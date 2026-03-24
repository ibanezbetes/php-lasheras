<?php 
    $idRol='';
    $rolesUsuario=array();
    extract($datos);
    require 'VPermisosFunciones.php';  //contiene las funciones que pintas las opciones y permisos
    echo pintaFiltroRoles(array('roles'=>$datos['roles'], 
                                'idRol'=>$idRol,
                                'rolesUsuario'=>$rolesUsuario)); 
?>
