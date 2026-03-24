<?php
	//inicializar variables recibidas en $datos.
    $idOpcionAnterior=''; //no se recibe cuando editamos
	$idOpcion='';
	$idPadre=0;
	$texto='';
	$accion='';
    $orden=0;
    $activo='S';
    $idCapa=0;
	extract($datos); 
	//echo json_encode($datos);
    $html='<br><div class="divEdicionInterna">
            <form id="formEdicionOpcion" name="formEdicionOpcion">';

    if($idOpcionAnterior==''){
        $html.='<span><b>MODIFICANDO OPCIÓN</b> (Orden: '.$orden.')</span><br>';
    }else{
        $html.='<span><b>CREANDO NUEVA OPCIÓN</b> (Orden: '.$orden.')</span><br>';
    }

    $html.=    '<input type="hidden" id="idOpcionAnterior" name="idOpcionAnterior" value="'.$idOpcionAnterior.'">
                <input type="hidden" id="idOpcion" name="idOpcion" value="'.$idOpcion.'">
                <input type="hidden" id="idPadre" name="idPadre" value="'.$idPadre.'">
                <input type="hidden" id="orden" name="orden" value="'.$orden.'">
                <label for="texto">Texto:<br>
                    <input type="text" class="form-control" id="texto" name="texto" size="40" value="'.$texto.'">
                </label><br>
                <label for="accion">Acción:<br>
                    <input type="text" class="form-control" id="accion" name="accion" size="60" value="'.$accion.'">
                </label><br>
                <label for="activo">Activo:<br>
                    <select type="text" class="form-control" id="activo" name="activo">
                        <option value="S" ';
                            if($activo=='S') $html.= ' selected ';
    $html.=                 '>Activo</option>
                        <option value="N" ';
                            if($activo=='N') $html.= ' selected ';
    $html.=                 '>Inactivo</option>
                    </select>
                </label><br>
                <label for="publica">Publica:<br>
                    <select type="text" class="form-control" id="publica" name="publica">
                        <option value="S" ';
                            if($activo=='S') $html.= ' selected ';
    $html.=                 '>SI</option>
                        <option value="N" ';
                            if($activo=='N') $html.= ' selected ';
    $html.=                 '>NO</option>
                    </select>
                </label><br><br>
                <button type="button" class="btn btn-secondary" onclick="limpiarDivs(\'.divEdicion\')">Cancelar</button>&nbsp;&nbsp;
                <button type="button" class="btn btn-primary" onclick="guardarOpcion(\''.$idCapa.'\')">Guardar</button>
                <span id="msjForm" style="color:red;"></span>
                <br><br>
            </form>
        </div><br>';
    echo $html;
 ?>
