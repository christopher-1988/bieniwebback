<?php
    //CROSS-REEFRENCE PERMITIDOS
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('content-type: application/json; charset=utf-8');
	include_once("../config/config.php");
	include_once("funciones.php");
	
    list($METHOD,$PARAMS)=router($_SERVER['REQUEST_METHOD'],$_REQUEST['op']);

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
	}
	
	function formulario(){
	    
	    $data['correo'] = (!empty($_REQUEST['correo']) ? $_REQUEST['correo'] :'');
	    $data['clave']  = (!empty($_REQUEST['clave']) ? $_REQUEST['clave'] :'');
	    
	    return $data;
    }
    
	function doLoginWithGoogle(){
        global $mysqli;
        $data = formulario();
    
	    $sentencia = $mysqli->prepare("SELECT u.id AS idusuario,p.id AS idpersonal,CONCAT(p.nombre,' ',p.apellido) AS nombre,p.imagen
            FROM usuarios u
            INNER JOIN personal p ON p.idusuario=u.id
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
        	        'idpersonal'=> $row['idpersonal'],
        	        'nombre'    => $row['nombre']);
				
    	        echo notificacion(1,"Bienvenido.!","",$item);
    	    }
    	}
	}
	
    function dologinWithCredencial(){
        global $mysqli;
        $data   = formulario();
        $clave  = cifrarPassword($data['clave']);
        
        $sentencia = $mysqli->prepare("SELECT u.id AS idusuario,p.id AS idpersonal,CONCAT(p.nombre,' ',p.apellido) AS nombre,p.imagen
            FROM usuarios u
            INNER JOIN personal p ON p.idusuario=u.id
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
        	        'idpersonal'=> $row['idpersonal'],
        	        'nombre'    => $row['nombre']);
				
    	        echo notificacion(1,"Bienvenido.!","",$item);
    	    }
    	}
    }
  
?>