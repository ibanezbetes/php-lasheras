function limpiarDivs(filtro){ //Usado para cancelar ediciones
	document.querySelectorAll(filtro).forEach(
		function(ele){
			ele.innerHTML = '';
		}
	)
} //FIN limpiarDivs()

function obtenerVistaConParametros(controlador, metodo, destino, $posicion, datos){
    let parametros="controlador="+controlador+"&metodo="+metodo+"&"+datos;
    let opciones = {method: "GET",};
    fetch("CFrontal.php?"+parametros, opciones)
        .then(res =>{
            if(res.ok){
                return res.text();
            }
        })
        .then(vista =>{
			if(destino==''){
				console.log('destiono 20:'+destino);
				return vista; //para pintarla en la llamada
			}else{
				console.log('destiono 22:'+destino);
				document.getElementById(destino).style.display = 'block';
				//document.getElementById(destino).innerHTML = vista;
				if($posicion=='outerHTML' || $posicion==''){
					document.getElementById(destino).outerHTML = vista;
				}
				if($posicion=='insertAdjacentHTML' || $posicion==''){
					document.getElementById(destino).insertAdjacentHTML = vista;
				}
			}

        })
        .catch(err =>{
            document.getElementById(destino).innerHTML = 'Se ha producido un error, vuelva a buscar';
        })

} //FIN obtenerVistaConParametros()


function nuevaOpcion(idOpcionAnterior, idPadre){
	limpiarDivs('.divEdicion'); //vaciar todas las capas de edicion

	let opciones = { method: "GET",};  //sin opciones, por defecto es GET
    let parametros = "controlador=Permisos&metodo=getVistaEdicionOpcion";
	parametros+='&idCapa='+idOpcionAnterior;
	parametros+='&metodo=getVistaEdicionOpcion';
	parametros+='&idOpcion=&idPadre='+idPadre+'&idOpcionAnterior='+idOpcionAnterior;
    fetch("CFrontal.php?"+parametros, opciones)
        .then(res =>{
            if( res.ok ){
                return res.text();  
            }            
            throw new Error(res.status); //elevar el error.
        })
        .then(vista =>{
            document.getElementById('div_NuevaOpcion_'+idOpcionAnterior+'_'+idPadre).innerHTML = vista;
            document.getElementById('texto').focus();
        })
        .catch(err =>{
            console.error("Error al realizar la petición", err.message)
        });
} //FIN nuevaOpcion()

function editarOpcion(idOpcion){
	limpiarDivs('.divEdicion'); //vaciar todas las capas de edicion

	let opciones = { method: "GET",};  //sin opciones, por defecto es GET
    let parametros = "controlador=Permisos&metodo=getVistaEdicionOpcion";
	parametros+='&idOpcion='+idOpcion;

    fetch("CFrontal.php?"+parametros, opciones)
        .then(res =>{
            if( res.ok ){
                return res.text();  
            }            
            throw new Error(res.status); //elevar el error.
        })
        .then(vista =>{
            document.getElementById('divEdicion_'+idOpcion).innerHTML = vista;
            document.getElementById('texto').focus();
        })
        .catch(err =>{
            console.error("Error al realizar la petición", err.message)
        });
} //FIN editarOpcion()

function guardarOpcion(idCapa){
    let opciones = { method: "GET",};  //sin opciones, por defecto es GET
    let parametros = "controlador=Permisos&metodo=guardarOpcion";
    parametros += "&"+new URLSearchParams(new FormData(document.getElementById('formEdicionOpcion'))).toString();
    fetch("CFrontal.php?"+parametros, opciones)
        .then(res =>{
            if( res.ok ){
                return res.json();  
            }else{
                return 'ERR al buscar';
            }
        })
        .then(respuesta =>{
			let idOpcionAnterior=document.getElementById('idOpcionAnterior').value;
			if(respuesta.correcto=='S'){
				var idOpcion=document.getElementById('idOpcion').value;
				var idPadre=document.getElementById('idPadre').value;

				//Cerrar contenido capa edición
				limpiarDivs('.divEdicion'); 
				if(idOpcion==''){
					//nueva opcion
					let datos='idOpcion='+respuesta.idOpcion+'&ver=editada&idPadre='+idPadre;
					var vista = obtenerVistaConParametros('Permisos', 'getVistaOpcion', 'div_NuevaOpcion_'+idOpcionAnterior+'_'+idPadre, 'insertAdjacentHTML', datos);
				}else{
					//Recargar capa de opcion editada
					let datos='idOpcion='+idOpcion+'&ver=editada&idPadre='+idPadre;
					obtenerVistaConParametros('Permisos', 'getVistaOpcion', 'div_'+idCapa, '', datos);
				}
			}else{
				document.getElementById('msjForm').innerHTML('Se ha producido un error al guardar.');
			}
        })
        .catch(err =>{
            document.getElementById('msjForm').innerHTML = 'Se ha producido un error, vuelva intentarlo';
        });
} //FIN guardarOpcion()

function borrarPermiso(idPermiso){
	let opciones = { method: "GET",};  //sin opciones, por defecto es GET
    let parametros = "controlador=Permisos&metodo=eliminarPermiso";
	parametros+='&idPermiso='+idPermiso;

    fetch("CFrontal.php?"+parametros, opciones)
        .then(res =>{
            if( res.ok ){
                return res.json();  
            }            
            throw new Error(res.status); //elevar el error.
        })
        .then(respuesta =>{
			if(respuesta.eliminado=='S'){
				document.getElementById('divPer_'+respuesta.idPermiso).remove();
			}
        })
        .catch(err =>{
            console.error("Error al realizar la petición", err.message)
        });
} //FIN borrarPermiso()

function eliminarOpcion(idOpcion){
	if(confirm('¿Seguro que deseas eliminar la opcion y sus permisos?')){
		let opciones = { method: "GET",};  //sin opciones, por defecto es GET
		let parametros = "controlador=Permisos&metodo=eliminarOpcion";
		parametros+='&idOpcion='+idOpcion;
	
		fetch("CFrontal.php?"+parametros, opciones)
			.then(res =>{
				if( res.ok ){
					return res.json();  
				}            
				throw new Error(res.status); //elevar el error.
			})
			.then(respuesta =>{
				if(respuesta.eliminado=='S'){
					document.getElementById('div_'+idOpcion).remove();
					document.getElementById('div_nop_'+idOpcion).remove();
				}
			})
			.catch(err =>{
				console.error("Error al realizar la petición", err.message)
			});
	}
} //fin eliminarOpcion()


function editarPermiso(idOpcion, idPermiso){
	limpiarDivs('.divEdicion'); //vaciar todas las capas de edicion

	let opciones = { method: "GET",};  //sin opciones, por defecto es GET
    let parametros = "controlador=Permisos&metodo=getVistaEdicionPermiso";
	parametros+='&idOpcion='+idOpcion+'&idPermiso='+idPermiso;
	parametros+='&idCapa='+idOpcion;

    fetch("CFrontal.php?"+parametros, opciones)
        .then(res =>{
            if( res.ok ){
                return res.text();  
            }            
            throw new Error(res.status); //elevar el error.
        })
        .then(vista =>{
            document.getElementById('divEdicionPermiso_'+idOpcion).innerHTML = vista;
            document.getElementById('texto').focus();
        })
        .catch(err =>{
            console.error("Error al realizar la petición", err.message)
        });
}


function guardarPermiso(idCapa){
	document.querySelectorAll('.inputRed').forEach(function(inps){
		inps.classList.remove('inputRed');
	})
	document.getElementById('msjForm').innerHTML = '';

	if( document.getElementById('permiso').value=='') {
		document.getElementById('permiso').classList.add('inputRed');
	};
	if( document.getElementById('numPermiso').value=='') {
		document.getElementById('numPermiso').classList.add('inputRed');
	};
	if( document.querySelectorAll('.inputRed').length>0){
		document.getElementById('msjForm').innerHTML='Revisar los campos en rojo.';
	}else{ //correcto
		let opciones = { method: "GET",};  //sin opciones, por defecto es GET
		let parametros = "controlador=Permisos&metodo=guardarPermiso";
		parametros += "&"+new URLSearchParams(new FormData(document.getElementById('formEdicionPermiso'))).toString();
		fetch("CFrontal.php?"+parametros, opciones)
			.then(res =>{
				if( res.ok ){
					return res.json();  
				}else{
					return 'ERR al buscar';
				}
			})
			.then(respuesta =>{
				if(respuesta.correcto=='S'){
					//Recargar vista de permisos
					var parametros='&controlador=Permisos';
					parametros+='&metodo=getVistaPermisos';
					parametros+='&idOpcion='+respuesta.idOpcion;

					fetch("CFrontal.php?"+parametros, opciones)
					.then(res =>{
						if( res.ok ){
							return res.text();  
						}            
						throw new Error(res.status); //elevar el error.
					})
					.then(vista =>{
							limpiarDivs('.divEdicion'); //Cerrar contenido capa edición
							obtenerVistaConParametros('Permisos', 'getVistaOpcion', 'div_'+respuesta.idOpcion, '','idOpcion='+respuesta.idOpcion);
						})
						.catch(err =>{
							console.error("Error al realizar la petición", err.message)
						});
					//FIN recargar vista permisos
				}else{
					document.getElementById('msjForm').innerHTML='Se ha producido un error al guardar.';
				}
			})
			.catch(err =>{
				document.getElementById(msjForm).innerHTML = 'Se ha producido un error, vuelva intentarlo';
			});
	} //Fin verificacion inputs correcta
}


function editarRol(operacion){
	limpiarDivs('.divEdicion'); //vaciar todas las capas de edicion
	
	let opciones = { method: "GET",};  //sin opciones, por defecto es GET
    let parametros = "controlador=Permisos&metodo=getVistaEdicionRol&operacion="+operacion;
	if(operacion=='editar'){
		parametros+='&idRol='+document.getElementById('fidRol').value;
	}else{
		parametros+='&idRol=';
	}

    fetch("CFrontal.php?"+parametros, opciones)
        .then(res =>{
            if( res.ok ){
                return res.text();  
            }            
            throw new Error(res.status); //elevar el error.
        })
        .then(vista =>{
            document.getElementById('div_EdicionRol').innerHTML = vista;
            document.getElementById('rol').focus();
        })
        .catch(err =>{
            console.error("Error al realizar la petición", err.message)
        });
} // FIN editarRol()

function recargarFiltroRoles(idRol){
	let opciones = { method: "GET",};  //sin opciones, por defecto es GET
	var parametros='&controlador=Permisos';
		parametros+='&metodo=getVistaFiltroRoles';
		parametros+='&idRol='+idRol;
		parametros+='&idUsuario='+document.getElementById('fidUsuario').value;

		fetch("CFrontal.php?"+parametros, opciones)
			.then(res =>{
				if( res.ok ){
					return res.text();  
				}            
				throw new Error(res.status); //elevar el error.
			})
			.then(vista =>{
				document.getElementById('div_FiltroRoles').innerHTML = vista;
			establecerIconosAsignacionRoles();
			})
			.catch(err =>{
				console.error("Error al realizar la petición", err.message)
			});
}//FIN recargarFiltroRoles()


function establecerIconosAsignacionRoles(){
	if( document.getElementById('fidUsuario').value=='' || document.getElementById('fidRol').value==''){ //no seleccionado usuario y rol, ni poner, ni quitar
		document.getElementById('imgAsignarRol').style.display='none';
		document.getElementById('imgQuitarRol').style.display='none';
	}else{
		
		if(document.getElementById('fidRol').options[document.getElementById("fidRol").selectedIndex].text.indexOf('(*)')>0){ //marcado como asignado, permitir desasignar
			document.getElementById('imgQuitarRol').style.display='inline';
			document.getElementById('imgAsignarRol').style.display='none';
		}else{ //permiso no marcado como asignado, permitir asignar
			document.getElementById('imgAsignarRol').style.display='inline';
			document.getElementById('imgQuitarRol').style.display='none';
		}
	}
}//FIN establecerIconosAsignacionRoles()



function guardarRol(){
	if(document.getElementById('rol').value==''){
		document.getElementById('rol').classList.add('inputRed');
		document.getElementById('msjForm').innerHTML='Debe rellenar el campo "Rol".';
	}else{
		let opciones = { method: "GET",};  //sin opciones, por defecto es GET
		let parametros = "controlador=Permisos&metodo=guardarRol";
		parametros+='&idRol='+document.getElementById('idRol').value;
		parametros+='&rol='+document.getElementById('rol').value;
		fetch("CFrontal.php?"+parametros, opciones)
			.then(res =>{
				if( res.ok ){
					return res.json();  
				}else{
					return 'ERR al buscar';
				}
			})
			.then(respuesta =>{
				if(respuesta.correcto=='S'){
					limpiarDivs('.divEdicion'); //vaciar todas las capas de edicion
					//Recargar capa de Rol
					recargarFiltroRoles(respuesta.idRol);
				}else{
					document.getElementById('msjForm').innerHTML='Se ha producido un error al guardar.';
				}
			})
			.catch(err =>{
				console.log('Se ha producido un error, vuelva intentarlo');
			});
	}
}// guardarRol()

function seleccionadoUsuario(){
	let opciones = { method: "GET",};  //sin opciones, por defecto es GET
	var parametros ='&controlador=Permisos';
	parametros+='&idUsuario='+document.getElementById('fidUsuario').value;
	parametros+='&idRol='+document.getElementById('fidRol').value;
	parametros+='&metodo=getVistaFiltroRoles';

	fetch("CFrontal.php?"+parametros, opciones)
		.then(res =>{
			if( res.ok ){
				return res.text();  
			}            
			throw new Error(res.status); //elevar el error.
		})
		.then(vista =>{
			document.getElementById('div_FiltroRoles').outerHTML = vista;
			establecerIconosAsignacionRoles();
		})
		.catch(err =>{
			console.error("Error al realizar la petición", err.message)
		});
}//FIN seleccionadoUsuario()


function asignarRol(){
	let opciones = { method: "GET",};
	var parametros ='&controlador=Permisos';
	parametros+='&idUsuario='+document.getElementById('fidUsuario').value;
	parametros+='&idRol='+document.getElementById('fidRol').value;
	parametros+='&metodo=asignarRolUsuario';

	fetch("CFrontal.php?"+parametros, opciones)
		.then(res =>{
			if( res.ok ){
				return res.text();  
			}            
			throw new Error(res.status); //elevar el error.
		})
		.then(vista =>{
			recargarFiltroRoles( document.getElementById('fidRol').value );
		})
		.catch(err =>{
			console.error("Error al realizar la petición", err.message)
		});
}// FIN asignarRol()

function quitarRol(){
	let opciones = { method: "GET",};
	var parametros ='&controlador=Permisos';
	parametros+='&idUsuario='+document.getElementById('fidUsuario').value;
	parametros+='&idRol='+document.getElementById('fidRol').value;
	parametros+='&metodo=quitarRolUsuario';
	fetch("CFrontal.php?"+parametros, opciones)
		.then(res =>{
			if( res.ok ){
				return res.text();  
			}            
			throw new Error(res.status); //elevar el error.
		})
		.then(vista =>{
			recargarFiltroRoles( document.getElementById('fidRol').value );
		})
		.catch(err =>{
			console.error("Error al realizar la petición", err.message)
		});
}//FIN quitarRol()


function seleccionadoRol(){
	establecerIconosAsignacionRoles();
}


function clickPermisoRol(idPermiso){
	let opciones = { method: "GET",};
	var parametros ='&controlador=Permisos';
	parametros+='&idPermiso='+idPermiso;
	parametros+='&idRol='+document.getElementById('fidRol').value;
	parametros+='&marcado='+document.getElementById('pr_'+idPermiso).checked;
	parametros+='&metodo=setPermisoRol';
	fetch("CFrontal.php?"+parametros, opciones)
		.then(res =>{
			if( res.ok ){
				return res.text();  
			}            
			throw new Error(res.status); //elevar el error.
		})
		.then(respuesta =>{
			//nada especial
		})
		.catch(err =>{
			console.error("Error al realizar la petición", err.message)
		});
}//FIN clickPermisoRol()

function clickPermisoUsuario(idPermiso){
	let opciones = { method: "GET",};
	var parametros ='&controlador=Permisos';
	parametros+='&idPermiso='+idPermiso;
	parametros+='&idUsuario='+document.getElementById('fidUsuario').value;
	parametros+='&marcado='+document.getElementById('pu_'+idPermiso).checked;
	parametros+='&metodo=setPermisoUsuario';
	fetch("CFrontal.php?"+parametros, opciones)
		.then(res =>{
			if( res.ok ){
				return res.text();  
			}            
			throw new Error(res.status); //elevar el error.
		})
		.then(respuesta =>{
			//nada especial
		})
		.catch(err =>{
			console.error("Error al realizar la petición", err.message)
		});
}// FIN clickPermisoUsuario()


function eliminarRol(){
	let opciones = { method: "GET",};
	if(document.getElementById('fidRol').value==''){
		alert('Debes seleccionar un rol.');
	}else{
		if(confirm('¿Seguro de eliminar el rol: '+document.getElementById('fidRol').options[document.getElementById('fidRol').selectedIndex].text+'?')){
			var parametros='&controlador=Permisos';
			parametros+='&metodo=eliminarRol';
			parametros+='&idRol='+document.getElementById('fidRol').value;
			fetch("CFrontal.php?"+parametros, opciones)
				.then(res =>{
					if( res.ok ){
						return res.text();  
					}            
					throw new Error(res.status); //elevar el error.
				})
				.then(respuesta =>{
					recargarFiltroRoles('');
				})
				.catch(err =>{
					console.error("Error al realizar la petición", err.message)
				});
		}
	}
}//FIN eliminarRol()
