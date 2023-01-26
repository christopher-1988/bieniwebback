<?php
    include_once("../config/config.php");
    include_once("funciones.php");
	
    list($METHOD,$PARAMS)=router($_SERVER['REQUEST_METHOD'],$_REQUEST['op']);
    
    if ($METHOD =='GET'){
        switch($PARAMS){ 
            case 'usuarios':
                usuarios();
                break;
			case 'usuarioId':
                usuarioId();
                break;
            default:
                echo "{failure-GET:true}";
                break;
            
        }
    }
    
    if ($METHOD =='POST') {
        switch($PARAMS){
    	    case 'usuario':
                usuarioAdd();
                break;
    	    default:
                echo "{failure-POST:true}";
                break;
        }
    }
    
	if ($METHOD =='PUT') {
        switch($PARAMS){ 
			case "usuario" :
    			 usuarioEdit();
    			 break;
    	    default:
                echo "{failure-PUT:true}";
                break;
        } 
	}
	
	if ($METHOD =='DELETE') {
        switch($PARAMS){ 
			case "usuario":
    			 usuarioDelete();
    			 break;
    	    default:
                echo "{failure-DELETE:true}";
                break;
        } 
	} 
    
	function formulario(){
	    //$data = json_decode(file_get_contents("php://input"), true);
		//$INPUT  = file_get_contents("php://input");
		//$_REQUEST = json_decode($INPUT,true);
        $data['id']	= (!empty($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $data['nombre']	= (!empty($_REQUEST['nombre']) ? $_REQUEST['nombre']:'');
        $data['apellido'] = (!empty($_REQUEST['apellido']) ? $_REQUEST['apellido']:'');
		$data['correo']	= (!empty($_REQUEST['correo']) ? $_REQUEST['correo']:'');
		$data['clave']	= (!empty($_REQUEST['clave']) ? $_REQUEST['clave']:'');
		$data['telefono'] = (!empty($_REQUEST['telefono']) ? $_REQUEST['telefono']:'');
		$data['fecha'] = date('Y/m/d');
        $data['datetime'] = date("Y-m-d H:i:s");
		$data['busqueda'] = (!empty($_REQUEST['busqueda']) ? $_REQUEST['busqueda'] : '');
	    
	    return $data;
    }
    
    function params(){
	    $data['id']	= (!empty($_GET['id']) ? $_GET['id'] : '');
	    $data['page'] = (!empty($_REQUEST['page']) ? $_REQUEST['page'] : 1);
	    return $data;
    }
    
    function usuarios(){
        global $mysqli;
        $data = formulario();
        $response = array();
        
        $query  = " SELECT u.id AS idusuario,p.id AS idpersonal,CONCAT(p.nombre,' ',p.apellido) AS nombre,u.correo,p.imagen,u.estado
            FROM usuarios u 
            INNER JOIN personal p ON p.idusuario=u.id
            WHERE u.estado = 'activo'";
                    
        $result  = $mysqli->query($query);
		$recordsResults = $result->num_rows;
        //debugL($query,"usurios");
        if($recordsResults == 0){
            echo response($response,0,0,0);
            exit; 
        }
        
        while($row = $result->fetch_assoc()){  
            $response[] = array(
                    'id'            => $row["idusuario"],
                    'idpersonal'    => $row["idpersonal"],
                    'nombre'        => $row["nombre"],
                    'correo'        => $row["correo"],
					'imagen'   		=> $row["imagen"],
					'estado'   		=> $row["estado"]);
        }
        
        echo response($response,$recordsResults,0,0);
    }
	
	function usuarioId(){
        global $mysqli;
        $data = params();
        $response = array();
        
        $query = " SELECT u.id,p.nombre,p.apellido,u.correo,p.imagen,u.estado
            FROM usuarios u 
            INNER JOIN personal p ON p.idusuario=u.id
            WHERE u.id = '".$data['id']."' AND u.estado = 'activo'";
                    
        $result  = $mysqli->query($query);
		$recordsResults = $result->num_rows;
        //debugL($query,"usurios");
        if($recordsResults == 0){
            $response = array(
                    'nombre' => "",
                    'apellido' => "",
                    'correo' => "",
					'imagen' => "",
					'estado' => "");
					
            echo response($response,0,0,0);        
            exit; 
        }
        
        if($recordsResults > 0){
            
            while($row = $result->fetch_assoc()){  
                $response[] = array(
                    'nombre' => $row["nombre"],
                    'apellido' => $row["apellido"],
                    'correo' => $row["correo"],
					'imagen' => $row["imagen"],
					'estado' => $row["estado"]);
            }
        
            echo response($response,$recordsResults,0,0);    
        }
    }
    
    function usuarioAdd(){
        global $mysqli;
        $data = formulario();
        
        $query = "SELECT `id` FROM `usuarios` 
            WHERE correo ='".$data['correo']."'";
        
        $result = $mysqli->query($query);
		$row = $result->num_rows;
		//debugL($query."const","addfavorito");
		if($row > 0){		    
    		echo notificacion(2,"Este usuario ya existe","");
            exit;            
    	} else {    	  
    	    
    	    $query = " INSERT INTO `usuarios`(`id`, `correo`, `clave`, `telefono`, `nivel`, `condiciones`, `verificacion`, `verificacioncorreo`, `estado`, `fecha`) 
            VALUES (null,
            '".$data['correo']."',
            '".$data['clave']."',
            '".$data['telefono']."',
            '2',
            'si',
            'si',
            'si',
            'activo',
            '".$data['datetime']."')";
    		$result = $mysqli->query($query);
		    debugL($query."usuario","usuario");
    		if($result==true){
    	        $id = $mysqli->insert_id;
    	        $imagen = "";
    	        
    		    $queryP = "INSERT INTO `personal`(`id`, `idusuario`, `nombre`, `apellido`, `imagen`, `creado`) 
    		        VALUES (null,
    		        '".$id."',
    		        '".$data['nombre']."',
    		        '".$data['apellido']."',
    		        '".$imagen."',
    		        '".$data['datetime']."')";
    		    
    		    $result = $mysqli->query($query);
		        debugL($query."personal","usuario");
    		    if($result==true){
    		        $idP = $mysqli->insert_id;
    		        echo notificacion(1,"Error al crear el personal",$idP);
                    exit;
    		    }else{
                    echo notificacion(2,"Error al crear el personal","");
                    exit;
    		    }
    		}else{
    			echo notificacion(2,"Problema al agregar el afiliado","");
                exit;
    		}
    	}
    }
    
    function usuarioEdit(){
        global $mysqli;		
        $data   = formulario();
        
        $query=" UPDATE `afiliados` SET
                `nombre`='".$data['nombre']."',
                `descripcion`='".$data['descripcion']."',
                `estatus`='".$data['estatus']."'
                WHERE `id` = '".$data['id']."'";
            
        $result = $mysqli->query($query);
	    debugL($query,"addpaciente");
        if($result == true){
            echo json_encode(array(
                    'id'        => "",
                    'rsp'       => 1,
                    'msg'       => "Afiliado actualizado de forma exitosa ".$senadis));
            exit;    
        }else{
            echo notificacion(2,"Problema al actualizar el afiliado","");
            exit;
        }
    }
	
	function usuarioDelete(){
        global $mysqli;
        $data = formulario();
        
        $query = " DELETE FROM `usuarios` WHERE id = '".$data['id']."'";
    	$result = $mysqli->query($query);
    	//debugL($query."delet","usuario");
    	if($result==true){
    	    "DELETE FROM `personal` WHERE idusuario = '".$data['id']."'";
    	    echo notificacion(1,"Usuario eliminado de forma exitosa","");
                exit;
    	}else{
    	    echo notificacion(2,"Usuario al eliminar el afiliado","");
            exit;
        }
    }
?>