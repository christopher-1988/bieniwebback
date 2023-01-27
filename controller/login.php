<?php
	include_once("../config/config.php");
	include_once("../model/router.php");
	include_once("funciones.php");
	
    /*list($METHOD,$PARAMS)=router($_SERVER['REQUEST_METHOD'],$_REQUEST['op']);

    if ($METHOD =='GET'){
        switch($PARAMS){ 
            case 'getCuentas':
                getCuentas();
                break;
            default:
                echo "{failure-GET:true}";
                break;
        }
    }
    
    if($METHOD =='POST') {
        switch($PARAMS){ 
    	case "dologinWithCredencial":
    		dologinWithCredencial();
    		break;
    	case "doLoginWithGoogle":
            doLoginWithGoogle();
            break;
    	default:
            echo "{failure-POST:true}";
            break;
        } 
	}*/

	$router = new Router();
	
	function formulario(){
	    
	    $data['correo'] = (!empty($_REQUEST['correo']) ? $_REQUEST['correo'] :'');
	    $data['clave']  = (!empty($_REQUEST['clave']) ? $_REQUEST['clave'] :'');
	    
	    return $data;
    }
    
    //function doLoginWithGoogle(){
    $router->post('doLoginWithGoogle',function(){
        global $mysqli;
        $data = formulario();
    
	    $sentencia = $mysqli->prepare("SELECT u.id AS idusuario,CONCAT(u.nombre,' ',u.apellido) AS nombre,u.imagen,u.nivel
            FROM usuariosback u
            WHERE u.correo = ?");
            
        $sentencia->bind_param("s",$data['correo']);
    	$sentencia->execute();
    	
    	$resultado = $sentencia->get_result();
    	$recodTotals = $resultado->num_rows;
    	
    	if($recodTotals == 0){ 
    	    echo notificacion(2,"No existe un usuario asociado a este correo","");
    	}
    	
    	if($recodTotals > 0){
    	    if ($row = $resultado->fetch_assoc()) {
    	    
    	        $item = array(
				    'id'        => $row['idusuario'],
        	        'nombre'    => $row['nombre'],
        	        'imagen'    => $row['imagen'],
        	        'r'         => $row['nivel']);
				
    	        echo notificacion(1,"Bienvenido.!","",$item);
    	    }
    	}
    });
	//}
	//function dologinWithCredencial(){
	$router->post('dologinWithCredencial',function(){
    
        global $mysqli;
        $data   = formulario();
        $clave  = cifrarPassword($data['clave']);
        
        $sentencia = $mysqli->prepare("SELECT u.id AS idusuario,CONCAT(u.nombre,' ',u.apellido) AS nombre,u.imagen,u.nivel
            FROM usuariosback u
            WHERE u.correo = ? AND u.clave = ? ");
        
        $sentencia->bind_param("ss",$data['correo'],$clave);
		$sentencia->execute();
    	
    	$resultado = $sentencia->get_result();
    	$recodTotals = $resultado->num_rows;
    	
    	if($recodTotals == 0){ 
    	    echo notificacion(2,"Usuario o clave incorrecta","");
    	}
    	
    	if($recodTotals > 0){
    	    if ($row = $resultado->fetch_assoc()) {
    	    
    	        $item = array(
				    'id'        => $row['idusuario'],
        	        'nombre'    => $row['nombre'],
        	        'imagen'    => $row['imagen'],
        	        'r'         => $row['nivel']);
				
    	        echo notificacion(1,"Bienvenido.!","",$item);
    	    }
    	}
	});
	//}
?>