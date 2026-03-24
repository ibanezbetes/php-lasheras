<?php 
//echo json_encode($_SESSION['permisos']); 
//$datos['roles];
//$datos['usuarios'];
require 'VPermisosFunciones.php';  //contiene las funciones que pintas las opciones y permisos
?>

<link rel="stylesheet" type="text/css" href="css/Permisos.css" />

<form id="formularioBuscar" name="formularioBuscar">
    <span class="titulo-permisos">Mantenimiento de Permisos y Menús</span><br>
	
	<div id="div_fid_Usuario" class="div_fid_Usuario">
		<?php
		//combo usuarios con select
		echo '<label for="fid_Usuario">Usuario:<br></label>
                    <select id="fidUsuario" name="fidUsuario" class="form-select form-select-sm"
                            style="width:25em !important" onchange="seleccionadoUsuario()">
                            <option value="">- seleccionar usuario -</option>';
                        foreach($datos['usuarios'] as $usuario){
                            echo '<option value="'.$usuario['idUsuario'].'">';
                            echo $usuario['apellido1'].' '.$usuario['apellido2'].', ';
                            echo $usuario['nombre'].'</option>';
                        }
		echo '</select>';
		?>
	</div>
	<?php 
		echo pintaFiltroRoles(array('roles'=>$datos['roles'])); 
	?>
	<div id="div_EdicionRol" class="divEdicion"></div>

	<input type="text" style="width:1px; border:none;"><br>
	<button type="button" class="btn btn-primary" onclick="buscar('Permisos', 'getVistaPermisosMtto', 'formularioBuscar', 'capaResultadosBusqueda');">Buscar</button>
	&nbsp;&nbsp;<span id="msjError" style="color:red"></span>
</form>
<div id="capaResultadosBusqueda"></div>
<div id="capaEdicionNuevo" style="display:none;">

<script type="text/javascript" >
	
</script>
