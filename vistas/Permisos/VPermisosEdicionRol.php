<?php
	//inicializar variables recibidas en $datos.
	$idRol='';
	$rol='';
	extract($datos['rol']);
    //no puedo usar FORM pues estoy dentro del de buscar
    $html='<div class="divEdicionRol">
                <input type="hidden" id="idRol" name="idRol" value="'.$idRol.'">
                <label for="rol">Rol:<br>
                    <input type="text" id="rol" name="rol" class="form-control form-control-sm" size="50" value="'.$rol.'">
                </label><br><br>
                <button type="button" class="btn btn-secondary" onclick="limpiarDivs(\'.divEdicion\')">Cancelar</button>&nbsp;&nbsp;
                <button type="button" class="btn btn-primary" onclick="guardarRol()">Guardar</button>
                <span id="msjForm" style="color:red;"></span>
        </div>';
    echo $html;
 ?>
