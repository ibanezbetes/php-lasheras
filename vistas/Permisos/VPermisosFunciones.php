<?php 
function pintarSubOpciones($parametros){
    $menu=array(); //Todas las opciones del menu
    $idPadre=0; //padre de las opciones a pintar
	$permisosMenu=array();
	$permisosRol=array();
	$permisosUsuario=array();
	$permisosUsuarioPorRoles=array();
	$rolesUsuario=array();
	$ver=''; 
    extract($parametros);

    $html='';
	$titulo='Crear nueva opción.';
	if($idPadre!=0) $titulo='Crear nueva subopción.';
	//boton de nueva opcion
	if($ver=='Menu'){
		$html.= '<div style="background-color:transparent;margin-left:2em;">';
		$html.=		'<img src="imagenes/addmenu.png" class="icon-action" title="'.$titulo.'"
					onclick="nuevaOpcion(\''.$idPadre.'\',\''.$idPadre.'\');" >';
		$html.=	'</div>';
		$html.= '<div id="div_NuevaOpcion_0_'.$idPadre.'" class="divEdicion"></div>';
	}
    foreach ($menu as $ind=>$opcion){
		$html.='<div id="div_'.$opcion['idOpcion'].'" class="divOpcionMenu">';
		$html.='<div id="div_datosOpcion_'.$opcion['idOpcion'].'" style="border: none;">';
        if($ver=='Menu' || $ver=='editada'){
			$html.= '&nbsp;<img src="imagenes/editar.png" class="icon-action" title="Modificar la opci&oacute;n."
                        onclick="editarOpcion(\''.$opcion['idOpcion'].'\');">&nbsp;&nbsp;';
			$html.= '&nbsp;<img src="imagenes/delete.png" class="icon-action" title="Eliminar la opci&oacute;n."
                        onclick="eliminarOpcion(\''.$opcion['idOpcion'].'\');">&nbsp;&nbsp;';
		}
        $html.=		'<span class="textoMenu" 
                    	title="id: '.$opcion['idOpcion'].'  &#10;Accion: '.$opcion['accion'].'">'.$opcion['idOpcion'].' - '.$opcion['texto'];

		$html.='</span>';
			
		$html.='</div>'; //div_datosOpcion_
		//pintar permisosMenu
		$html.= pintarPermisos(array('permisosMenu'=>$permisosMenu, 
									'idOpcion'=>$opcion['idOpcion'],
									'permisosRol'=>$permisosRol,
									'permisosUsuario'=>$permisosUsuario,
									'permisosUsuarioPorRoles'=>$permisosUsuarioPorRoles,
									'rolesUsuario'=>$rolesUsuario,
									'ver'=>$ver)); //lo paso por variables para que se vea mejor
		$html.='<div id="divEdicion_'.$opcion['idOpcion'].'" class="divEdicion"></div>';
			//pintar subopciones (el menu principal es un submenu del 0)
			if($opcion['idPadre']==0){ //solo si es el primer nivel
				$html.='<div id="div_subopciones_'.$opcion['idOpcion'].'" class="subopciones">';
				$parametosSubopciones=$parametros;
				if(!isset($opcion['subOpciones']))	$opcion['subOpciones']=array(); //para que pinte vacio
				$parametosSubopciones['menu']=$opcion['subOpciones'];
				$parametosSubopciones['idPadre']=$opcion['idOpcion'];
				$parametosSubopciones['permisosMenu']=$permisosMenu;
				$parametosSubopciones['permisosRol']=$permisosRol;
				$parametosSubopciones['permisosUsuario']=$permisosUsuario;
				$parametosSubopciones['permisosUsuarioPorRoles']=$permisosUsuarioPorRoles;
				$parametosSubopciones['rolesUsuario']=$rolesUsuario;
				
				if($ver=='editada') $parametosSubopciones['ver']='Menu';//..?
				$html.=pintarSubOpciones($parametosSubopciones);
				$html.='</div>';
			}
		$html.='</div>'; //div_
		if($ver=='Menu'){
			//boton de nueva opcion
			$idOpcionAnterior=$opcion['idOpcion'];
			$idPadreOpcionAnterior=$opcion['idPadre'];

			$html.='<div id="div_nop_'.$idOpcionAnterior.'" style="background-color:transparent;margin-left:2em;">';
			$html.=		'<img src="imagenes/addmenu.png" class="icon-action" title="Crear nueva opción"
							onclick="nuevaOpcion(\''.$idOpcionAnterior.'\',\''.$idPadreOpcionAnterior.'\');" >';
			$html.='</div>';
			$html.='<div id="div_NuevaOpcion_'.$idOpcionAnterior.'_'.$idPadre.'" class="divEdicion"></div>';
		}


        $orden=$opcion['orden'];
    }

    return $html;
}


function pintarPermisos($parametros=array()){
	$idOpcion='';
	$permisosMenu=array();  //permisos definidos para la opcion de menu
	$permisosUsuario=array();
	$permisosUsuarioPorRoles=array(); //$per[idPermiso][idRol]=idPermiso
	$rolesUsuario=array();
	$ver='';
	extract($parametros);

	$vistaP='';
	$vistaP.='<div id="divPermisos_'.$idOpcion.'" style="padding-left:4em;">';
	if( isset($permisosMenu[$idOpcion]) AND !empty($permisosMenu[$idOpcion]) ){
		foreach ($permisosMenu[$idOpcion] as $permiso){
			$numPermiso=$permiso['numPermiso'];
			$idPermiso= $permiso['idPermiso'];
            $vistaP.='<div id="divPer_'.$permiso['idPermiso'].'" class="item-permiso">';
			if($ver=='Usuario'){
				$vistaP.='<input type="checkbox"  id="pu_'.$idPermiso.'" onclick="clickPermisoUsuario(\''.$idPermiso.'\');" ';
				if(isset($permisosUsuario[$idPermiso])) $vistaP.=' checked ';
				$vistaP.='>';
			}
			if($ver=='Rol'){
				$vistaP.='<input type="checkbox"  id="pr_'.$idPermiso.'" onclick="clickPermisoRol(\''.$idPermiso.'\');" ';
				if(isset($permisosRol[$idPermiso])) $vistaP.=' checked ';
				$vistaP.='>';
			}
			$vistaP.='&nbsp;&nbsp;&nbsp;';
			if($ver=='Usuario'){
				//informar sobre si el usuario tiene el permiso mediante roles (y por cuales)
				if(isset($permisosUsuarioPorRoles[$idPermiso])){


						$listaRoles='';
						foreach ($permisosUsuarioPorRoles[$idPermiso] as $idRol=>$verdadero){
							$listaRoles.=$rolesUsuario[$idRol]['rol'].', ';
						}
						
						$vistaP.='<img src="imagenes/rol_azul.png" class="icon-action" title="Con permiso por rol: '.$listaRoles.'" onclick="';
						$vistaP.="alert('Con permiso por rol: ".$listaRoles."');";
						$vistaP.='" >';
						

				}else{
					$vistaP.='<img src="imagenes/rol_gris.png" class="icon-action" title="NO tiene asignado el permiso por roles.">';
				}
			}
			$vistaP.='&nbsp;&nbsp;&nbsp;';
            $vistaP.='<span style="font-weight:600; color:#34495e;">' . $permiso['numPermiso'] . ' - ' . $permiso['permiso'] . '</span>';
            if($ver=='Menu' || $ver=='editada'){
                //editar permiso
                $vistaP.='&nbsp;&nbsp;&nbsp;<img src="imagenes/editar.png" class="icon-action" title="Modificar el permiso."
                        onclick="editarPermiso(\''.$idOpcion.'\',\''.$permiso['idPermiso'].'\');">';
                //elimiar permiso
                $vistaP.='&nbsp;&nbsp;&nbsp;<img src="imagenes/delete.png" class="icon-action" title="Eliminar este permiso."
                                onclick="borrarPermiso(\''.$permiso['idPermiso'].'\');">';
            }
            $vistaP.='<br>';
            $vistaP.='</div>'; //divPer
		}
	}
	if($ver=='Menu' || $ver=='editada'){
		//add permiso
		$vistaP.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';				 
		$vistaP.='<img src="imagenes/addPermiso.png" class="icon-action" title="Crear un nuevo permiso."
					 onclick="editarPermiso(\''.$idOpcion.'\',\'0\' );">';
		$vistaP.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';				 
		$vistaP.='<div id="divEdicionPermiso_'.$idOpcion.'" class="divEdicion"></div>';
	}
	$vistaP.='</div>'; //divPermisos_
	return $vistaP;
} // fin pintarPermisos


function pintaFiltroRoles($datos){
	$rolesUsuario=array(); //se reciben si hay que marcar los roles que tiene un usuario
	$idRol='';
	extract($datos);
	
	//$datos['roles']
	$vistaFR='<div id="div_FiltroRoles" class="filtro-roles-container">';
	$vistaFR.=	'<div><label for="fidRol" class="fw-bold text-secondary mb-0 me-2" style="font-size: 0.9em;">Seleccionar Rol:</label>';
	$vistaFR.=		'<select id="fidRol" name="fidRol" class="form-select form-select-sm d-inline-block" style="width: auto;"
						onchange="seleccionadoRol();">';
	$vistaFR.=			'<option value="">- seleccionar rol -</option>';
	foreach($datos['roles'] as $rol){
		$vistaFR.=			'<option value="'.$rol['idRol'].'"  ';
		if($idRol==$rol['idRol']){
			$vistaFR.=' selected ';
		}
		if(isset($rolesUsuario[$rol['idRol']])){
			$rol['rol']=$rol['rol'].' (*)';
		}
		$vistaFR.=			'>'.$rol['rol'].'</option>';
	}
	$vistaFR.=	'	</select></div>';
	$vistaFR.= '<div class="ms-auto">';
	$vistaFR.= '<img src="imagenes/editar.png" class="icon-action ms-2" 
					title="Modificar el rol."
					onclick="editarRol(\'editar\' );">';
	$vistaFR.= '<img src="imagenes/addRol.png" class="icon-action ms-2" 
					title="Crear un nuevo rol."
					onclick="editarRol(\'nuevo\' );">';
	$vistaFR.= '<img src="imagenes/delete.png" class="icon-action ms-2" 
					title="Eliminar rol."
					onclick="eliminarRol();">';
	$vistaFR.='	<img src="imagenes/asignarRol.png" id="imgAsignarRol" class="icon-action ms-2"
				style="display:none;" 
				title="Asignar el rol al usuario." onclick="asignarRol();">
				<img src="imagenes/quitarRol.png" id="imgQuitarRol" class="icon-action ms-2"
				style="display:none;" 
				title="Quitar el rol al usuario." onclick="quitarRol();">';					
	$vistaFR.='</div></div>';
	return $vistaFR;
}
?>
