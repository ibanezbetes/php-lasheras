<?php
	//inicializar variables recibidas en $datos.
	$idOpcion='';
	$idPermiso='';
	$permiso='';
	$numPermiso='';
    $idCapa='';
    extract($datos);

	//echo json_encode($datos);
    $html='<div class="divEdicionInterna">
            <form id="formEdicionPermiso" name="formEdicionPermiso">
                <input type="hidden" id="idOpcion" name="idOpcion" value="'.$idOpcion.'">
                <input type="hidden" id="idPermiso" name="idPermiso" value="'.$idPermiso.'">
                <label for="permiso">Permiso:<br>
                    <input type="text" id="permiso" name="permiso" class="form-control" size="50" value="'.$permiso.'">
                </label><br>
                <label for="numPermiso">Num. Permiso:<br>
                    <input type="text" id="numPermiso" name="numPermiso" class="form-control" size="2" value="'.$numPermiso.'">
                </label><br>
                <br>
                <button type="button" class="btn btn-secondary" onclick="limpiarDivs(\'.divEdicion\')">Cancelar</button>&nbsp;&nbsp;
                <button type="button" class="btn btn-primary" onclick="guardarPermiso(\''.$idCapa.'\')">Guardar</button>
                <span id="msjForm" style="color:red;"></span>
            </form>
            <br>
        </div>';
    echo $html;
 ?>
