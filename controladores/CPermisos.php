<?php
    require_once 'controladores/Controlador.php';
    require_once 'vistas/Vista.php';
    require_once 'modelos/MPermisos.php';

    class CPermisos extends Controlador{
        private $modelo;

        public function __construct(){
            parent::__construct(); //ejecutar el constructor padre
            $this->modelo= new MPermisos();
        }

        //MENU HTML, sin BD
        public  function getVistaPermisosPrincipal(){
            $datos['roles']=$this->modelo->getRoles(array());
            require_once 'modelos/MUsuarios.php';
            $objUsu=new MUsuarios();
            $datos['usuarios']=$objUsu->buscarUsuarios();
            Vista::render('vistas/Permisos/VPermisosPrincipal.php', $datos);  
        }

        public  function getVistaPermisosMtto($filtros=array()){ 
            $datos=array();
            $datos['idRol']=$filtros['fidRol'];
            $datos['idUsuario']=$filtros['fidUsuario'];
            //obtener todas las opciones del menu
            $datos['menu']=$this->modelo->getDatosMenuTabla($filtros);
            $datos['ver']='Menu';
            if($datos['idRol']!='' && $datos['idUsuario']!=''){
                echo 'Para ver permisos, selecciona sólo el rol o el usuario.';
            }else{
                if($datos['idRol']!=''){
                    $datos['ver']='Rol';
                    $datos['permisosRol']=$this->modelo->getPermisosRol(array('idRol'=>$datos['idRol']));
                    $datos['datosRol']=$this->modelo->getRoles(Array('idRol'=>$datos['idRol']));
                }
                if($datos['idUsuario']!=''){
                    $datos['ver']='Usuario';
                    $datos['permisosUsuario']=$this->modelo->getPermisosUsuario(array('idUsuario'=>$datos['idUsuario']));
                    $datos['permisosUsuarioPorRoles']=$this->modelo->getPermisosUsuarioPorRoles(array('idUsuario'=>$datos['idUsuario']));
                    $datos['rolesUsuario']=$this->modelo->getRoles(array('idUsuario'=>$datos['idUsuario']));
                    require_once 'modelos/MUsuarios.php';
                    $objMUsu=new MUsuarios();
                    $datosUsuario=$objMUsu->buscarUsuarios(array('idUsuario'=>$datos['idUsuario'])) ;
                    $datos['usuario']=$datosUsuario[0]['apellido1'].' '.$datosUsuario[0]['apellido2'].', '.$datosUsuario[0]['nombre'];
                }
                $datos['permisosMenu']=$this->modelo->getPermisosMenu($filtros); 
                Vista::render('vistas/Permisos/VPermisosMtto.php', $datos);
            }
        }

    // FUNCIONES DE OPCIONES DE MENU
    public function getVistaEdicionOpcion($datos=array()){
        if($datos['idOpcion']>=1){
            $datosOpcionEditar=$this->modelo->getDatosOpcion($datos);
            $datosOpcionEditar['idCapa']=$datos['idOpcion'];
            Vista::render('vistas/Permisos/VPermisosEdicionOpcion.php', $datosOpcionEditar);
        }else{ //es para nueva opcion
            if($datos['idOpcionAnterior']==0){
                $datos['orden']=1;
            }else{
                $datosAnterior=$this->modelo->getDatosOpcion(array('idOpcion'=>$datos['idOpcionAnterior']));
                $datos['orden']=$datosAnterior['orden']+1;
            }
            Vista::render('vistas/Permisos/VPermisosEdicionOpcion.php', $datos);
        }
    }       

    public function guardarOpcion($datos=array()){
        $respuesta=Array();
        $respuesta['correcto']='S';
        $respuesta['msj']='';
        if($datos['idOpcion']=='' || $datos['idOpcion']=='0'){
            //nueva opcion
            $id=$this->modelo->insertarNuevaOpcion($datos);
            $respuesta['idOpcion']= $id;
            $respuesta['idOpcionAnterior']=$datos['idOpcionAnterior'];
            $respuesta['operacion']= 'insertar';
            
            //crear permisos basicos
            $permiso=array('idOpcion'=>$id, 'numPermiso'=>'1', 'permiso'=>'Consultar');
            $idP[1]=$this->modelo->insertarNuevoPermiso($permiso);
            $permiso=array('idOpcion'=>$id, 'numPermiso'=>'2', 'permiso'=>'Crear');
            $idP[2]=$this->modelo->insertarNuevoPermiso($permiso);
            $permiso=array('idOpcion'=>$id, 'numPermiso'=>'3', 'permiso'=>'Modificar');
            $idP[3]=$this->modelo->insertarNuevoPermiso($permiso);
            $permiso=array('idOpcion'=>$id, 'numPermiso'=>'4', 'permiso'=>'Eliminar');
            $idP[4]=$this->modelo->insertarNuevoPermiso($permiso);

            //asignar los permisos basicos al adminstrador
            foreach($idP as $idpermiso){
                $this->modelo->insertarPermisoRol(array('idPermiso'=>$idpermiso,
                                                    'idRol'=>$this->modelo->idRolAdministrador));
            }           
        }else{
            //modificando           
            $numAfectados=$this->modelo->actualizarOpcion($datos);
            $respuesta['idOpcion']= $datos['idOpcion'];
            if($numAfectados!=1 && $numAfectados===1){
                $respuesta['correcto']='N';
                $respuesta['msj']='No se ha podido modificar.';
            }
            $respuesta['operacion']= 'editando';
        }
        echo json_encode($respuesta);
    } //FIN guardarOpcion


    public function getVistaOpcion($datos=array()){
        $datos['crearAntes']='N'; //no mostrar el addOpcion antes.
        $datos['conSubopciones']='S'; //incluir las subopcines
        //me viene el id_Opcion y filtrará el menu, opcional: ver
        $datos['menu']=$this->modelo->getDatosMenuTabla($datos);
        if(!isset($datos['ver'])){
            $datos['ver']='editada'; //para no pintar iconos nueva opcion antes y despues
        }
        $datosPermisos=$datos;
        $datosPermisos['ver']='Menu';
        $datosPermisos['idOpcion']=$datos['idOpcion'];
        $datos['permisosMenu']=$this->modelo->getPermisosMenu($datosPermisos);
		Vista::render('vistas/Permisos/VPermisosPintaOpcion.php', $datos);
    }//FIN getVistaOpcion()

    public function eliminarOpcion($datos){
        $eli=$this->modelo->eliminarOpcion($datos);
        $respuesta['eliminado']='S';
        $respuesta['idOpcion']=$datos['idOpcion'];
        echo json_encode($respuesta);
    }

//FUNCIONES DE PERMISOS
    public function guardarPermiso($datos=array()){
        $respuesta=Array();
        $respuesta['correcto']='S';
        $respuesta['msj']='';
        $respuesta['idOpcion']=$datos['idOpcion'];
        if($datos['idPermiso']=='0'){
            //nueva opcion
            $id=$this->modelo->insertarNuevoPermiso($datos); 
            $respuesta['idPermiso']= $id;
            $respuesta['operacion']= 'insertar';
        }else{
            $numAfectados=$this->modelo->actualizarPermiso($datos);
            $respuesta['idPermiso']= $datos['idPermiso'];
            if($numAfectados!=1){
                $respuesta['correcto']='N';
                $respuesta['msj']='No se ha podido modificar.';
            }
            $respuesta['operacion']= 'editando';
            //modificando
        }
        echo json_encode($respuesta);
    } //FIN guardarPermiso()   
    
    public function eliminarPermiso($datos){
        $eli=$this->modelo->eliminarPermisoPorId($datos);
        $respuesta['eliminado']='S';
        $respuesta['idPermiso']=$datos['idPermiso'];
        echo json_encode($respuesta);
    }

    public function getVistaEdicionPermiso($datos=array()){
        if($datos['idPermiso']>=1){
			$datosPermisoEditar=$this->modelo->getPermisosMenu($datos);
            $datosPermisoEditar['idCapa']=$datos['idOpcion'];
            Vista::render('vistas/Permisos/VPermisosEdicionPermiso.php', $datosPermisoEditar[0]);
		}else{ //es para nuevo permiso
            Vista::render('vistas/Permisos/VPermisosEdicionPermiso.php', $datos);
        }
    }

    public function getVistaPermisos($datos){
        $datos['permisosMenu']=$this->modelo->getPermisosMenu($datos); //le pasamos id_Opcion
        $datos['ver']='Menu';
        Vista::render('vistas/Permisos/VPermisosPintaPermisos.php', $datos);

    }

//FUNCIONES DE ROLES

    public function getVistaEdicionRol($datos=array()){
        if($datos['operacion']=='editar' && $datos['idRol']==''){
            echo '<span style="color:blue;">Debes seleccionar un rol para editarlo.</span>';
        }else{
            if($datos['idRol']>=1){
                $datosRolEditar=$this->modelo->getRoles($datos);
                $datos['rol']=$datosRolEditar[$datos['idRol']];
            }else{ //es para nueva opcion
                $datos['rol']=array();
            }
            Vista::render('vistas/Permisos/VPermisosEdicionRol.php', $datos);
        }
    } // FIN getVistaEdicionRol()


    public function guardarRol($datos=array()){
        $respuesta=Array();
        $respuesta['correcto']='S';
        $respuesta['msj']='';
        if($datos['idRol']==''){
            //nuevo rol
            $id=$this->modelo->insertarNuevoRol($datos);
            $respuesta['idRol']= $id;
            $respuesta['operacion']= 'insertar';
        }else{
            //editando rol
            $numAfectados=$this->modelo->actualizarRol($datos);
            $respuesta['idRol']= $datos['idRol'];
            if($numAfectados!=1){
                $respuesta['correcto']='N';
                $respuesta['msj']='No se ha podido modificar.';
            }
            $respuesta['operacion']= 'editando';
            //modificando
        }
        echo json_encode($respuesta);
    }//FIN guardarRol()


    public function eliminarRol($datos){
        $eli=$this->modelo->eliminarRol($datos);
        $respuesta['eliminado']='S';
        echo json_encode($respuesta);
    }//FIN eliminarRol()


    public function getVistaFiltroRoles($datos=array()){
        $datos['roles']=$this->modelo->getRoles(array());
        $datos['rolesUsuario']=array();
        if($datos['idUsuario']!=''){
            $datos['rolesUsuario']=$this->modelo->getRoles(array('idUsuario'=>$datos['idUsuario']));
        }
        Vista::render('vistas/Permisos/VPermisosFiltroRol.php', $datos);
    }//FIN getVistaFiltroRoles()


    public function asignarRolUsuario($datos){
        $this->modelo->setRolUsuario($datos);
    }

    
    public function quitarRolUsuario($datos){
        $this->modelo->eliminarRolUsuario($datos);
    }

    public function setPermisoRol($datos){
        $respuesta='';
        if($datos['marcado']=='true'){
            $respuesta=$this->modelo->ponerPermisoRol($datos);
        }else{
            $respuesta=$this->modelo->quitarPermisoRol($datos);
        }
        echo $respuesta;
    }

//FUNCIONES PERMISOS USUARIO

    public function setPermisoUsuario($datos){
        $respuesta='';
        if($datos['marcado']=='true'){
            $respuesta=$this->modelo->ponerPermisoUsuario($datos);
        }else{
            $respuesta=$this->modelo->quitarPermisoUsuario($datos);
        }
        echo $respuesta;
    }//fin setPermisoUsuario()



} //FIN class CPermisos
