<?php
    include_once("../config/config.php");
    include_once("funciones.php");
	
    list($METHOD,$PARAMS)=router($_SERVER['REQUEST_METHOD'],$_REQUEST['op']);
    //debugL($PARAMS);
    if ($METHOD == 'GET'){
        switch($PARAMS){
			case 'pacientes':
				pacientes();
				break;
			case 'pacienteId':
				pacienteId();
				break;
            default:
                echo "{failure-GET:true}";
                break;
        }
    }elseif ($METHOD =='POST') {
        switch($PARAMS){
    	    case "paciente" :
    			cuentaAprobar();
    			break;
    	    default:
                echo "{failure-POST:true}";
                break;
        } 
	}elseif ($METHOD =='PUT') {
        switch($PARAMS){ 
			case "pacienete" :
    			cuentaAprobar();
    			break;
    	    default:
                echo "{failure-PUT:true}";
                break;
        } 
	}elseif ($METHOD =='DELETE') {
        switch($PARAMS){ 
			case "paciente" :
    			 deleteValidacion();
    			 break;
    	    default:
                echo "{failure-POST:true}";
                break;
        } 
	} 
    
	function formulario(){
		//$data = json_decode(file_get_contents("php://input"), true);
		//$JSON 		= file_get_contents("php://input");
		//$_REQUEST 	= json_decode($JSON,true);
		
        $data['id'] = (!empty($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $data['iddocumento'] = (!empty($_REQUEST['iddocumento']) ? $_REQUEST['iddocumento'] : '');
        $data['idfamiliar'] = (!empty($_REQUEST['idfamiliar']) ? $_REQUEST['idfamiliar'] : '');
        $data['idpaciente'] = (!empty($_REQUEST['idpaciente']) ? $_REQUEST['idpaciente'] : '');
        $data['idusuario'] = (!empty($_REQUEST['idusuario']) ? $_REQUEST['idusuario'] : '');
        $data['nombre'] = (!empty($_REQUEST['nombre']) ? $_REQUEST['nombre']:'');
        $data['descripcion'] = (!empty($_REQUEST['descripcion']) ? $_REQUEST['descripcion']:'');
		$data['estatus'] = (!empty($_REQUEST['estatus']) ? $_REQUEST['estatus']:'');
		$data['estado']	= (!empty($_REQUEST['estado']) ? $_REQUEST['estado']:'');
		$data['busqueda'] = (!empty($_REQUEST['busqueda']) ? $_REQUEST['busqueda'] : '');
	    
	    return $data;
    }
	
	function params(){
		
        $data['id']	  = (!empty($_GET['id']) ? $_GET['id'] : '');
        $data['page'] = (!empty($_GET['page']) ? $_GET['page'] : 1);
        
	    return $data;
    }
    //-VALIDADCION-DEPENDIENTE-----------------------------------------
    function pacientes(){
        global $mysqli;
        $data = params();
		$response = array();
       
        $query  = " SELECT p.id,p.idparentesco,tp.nombre AS tipodocumento,pd.documento,p.nombre, p.apellido,p.edad, p.fechanacimiento, p.gruposangre,p.numeroemergencia,p.imagen, p.discapacidad,pd.imagen_documento,IF(u.verificacioncorreo=0,'no','si') AS verificacioncorreo,u.telefono
            FROM pacientes p
            INNER JOIN usuarios u ON u.id=p.idusuario
            LEFT JOIN pacientes_documentos pd ON pd.idpaciente=p.id
            LEFT JOIN tipos_documento tp ON tp.id=pd.idtipodocumento
            WHERE p.id='".$data['id']."' AND pd.estado='activo ";
		
		if(!$result = $mysqliWallet->query($query)){
    		die($mysqliWallet->error);  
    	}
    	
    	$recordsTotals = $result->num_rows;
		$inicio  = $data['page'] * 10 - 10;   
    	$query  .= " ORDER BY b.fecha DESC LIMIT $inicio, 10 ";
    	$result  = $mysqliWallet->query($query);
    	$recordsFiltered = $result->num_rows;
        //debugL($query,"getValidaciones");
		if($recordsTotals == 0){
			echo response($response,0,0,0);
		}   
		
		if($recordsTotals > 0){
			while($row = $result->fetch_assoc()){        
		        $resultado[] = array(
            		'idparentesco'      => $row['idparentesco'],
    				'nombre'   			=> ucwords($row['nombre']), 
    				'tipodocumento'     => $row['tipodocumento'], 
    				'documento'         => $row['documento'], 
    				'fechanacimiento'   => $row['fechanacimiento'], 
    				'gruposangre'       => $row['gruposangre'], 
    				'numeroemergencia'  => $row['numeroemergencia'], 
    				'imagen'        => $row['imagen'], 
    				'edad'          => $row['edad'],
    				'telefono'      => $row['telefono'],
    				'discapacidad'  => $row['discapacidad'],
    				'imagendocumento'    => $row['imagen_documento'],
    				'verificacioncorreo' => $row['verificacioncorreo']);
			}
			echo response($response,$recordsTotals,$recordsFiltered,0);
		}        
    }
	
	function pacienteId(){
        global $mysqli;
        $data = params();
		$response = array();
       
        $query  = " SELECT p.id,p.idparentesco,tp.nombre AS tipodocumento,pd.documento,p.nombre, p.apellido,p.edad, p.fechanacimiento, p.gruposangre,p.numeroemergencia,p.imagen, p.discapacidad,pd.imagen_documento,IF(u.verificacioncorreo=0,'no','si') AS verificacioncorreo,u.telefono
            FROM pacientes p
            INNER JOIN usuarios u ON u.id=p.idusuario
            LEFT JOIN pacientes_documentos pd ON pd.idpaciente=p.id
            LEFT JOIN tipos_documento tp ON tp.id=pd.idtipodocumento
            WHERE p.id='".$data['id']."' AND pd.estado='activo ";
		
		if(!$result = $mysqliWallet->query($query)){
    		die($mysqliWallet->error);  
    	}
    	
    	$recordsTotals = $result->num_rows;
        //debugL($query,"pacientes");
		if($recordsTotals == 0){
			echo response($response,0,0,0);
		}   
		
		if($recordsTotals > 0){
			while($row = $result->fetch_assoc()){        
		        $resultado[] = array(
            		'idparentesco'      => $row['idparentesco'],
    				'nombre'   			=> ucwords($row['nombre']), 
    				'tipodocumento'     => $row['tipodocumento'], 
    				'documento'         => $row['documento'], 
    				'fechanacimiento'   => $row['fechanacimiento'], 
    				'gruposangre'       => $row['gruposangre'], 
    				'numeroemergencia'  => $row['numeroemergencia'], 
    				'imagen'        => $row['imagen'], 
    				'edad'          => $row['edad'],
    				'telefono'      => $row['telefono'],
    				'discapacidad'  => $row['discapacidad'],
    				'imagendocumento'    => $row['imagen_documento'],
    				'verificacioncorreo' => $row['verificacioncorreo']);
			}
			echo response($response,$recordsTotals,0,0);
		}        
    }
	
	function dependienteAprobar(){
        global $mysqli;		
        $data   = formulario();
		/*
		ESTADOS PACIENTE
		1-activo
		2-inactivo
		3-documento enviado
		*/
		$queryP = " UPDATE pacientes SET 
			idestado= 1 
			WHERE id='".$data['idpaciente']."'";
		/*
		ESTADOS DOCUMENTO VERIFICACION
		1-aprobado
		2-no aprobado
		3-en espera de aprobacion
		*/
        $queryD = " UPDATE pacientes_documentos SET
			idestadoverificacion = 1,
			estado = 'activo'
			WHERE id = '".$data['iddocumento']."'";
        /*
		ESTADOS DOCUMENTO VERIFICACION
		1-aprobado
		2-no aprobado
		3-en espera de aprobacion
		*/
        $queryF = " UPDATE relaciones_familiares SET 
            idestadoverificacion = 1
            WHERE id ='".$data['idfamiliar']."'";
            
        $resultP = $mysqli->query($queryP);
		$resultD = $mysqli->query($queryD);
        $resultF = $mysqli->query($queryF);
				
	    debugL($queryP,"dependiente");
	    debugL($queryD,"dependiente");
	    debugL($queryF,"dependiente");
	    
        if($resultP == true && $resultD == true && $resultF == true){
            echo notificacion(1,"Dependiente aprobado","");
            exit;    
        }else{
            echo notificacion(2,"Problema al actualizar la validación","");
            exit;
        }
    }
	
	function dependienteRechazar(){
        global $mysqli;		
        $data   = formulario();
        /*
		ESTADOS PACIENTE
		1-activo
		2-inactivo
		3-documento enviado
		*/
		$queryP = " UPDATE pacientes SET 
			idestado= 2 
			WHERE id='".$data['idpaciente']."'";
		/*
		ESTADOS DOCUMENTO VERIFICACION
		1-aprobado
		2-no aprobado
		3-en espera de aprobacion
		*/
        $queryD = " UPDATE pacientes_documentos SET
			idestadoverificacion = 2,
			estado = 'inactivo'
			WHERE id = '".$data['iddocumento']."'";
        /*
		ESTADOS DOCUMENTO VERIFICACION
		1-aprobado
		2-no aprobado
		3-en espera de aprobacion
		*/
        $queryF = " UPDATE relaciones_familiares SET 
            idestadoverificacion = 2
            WHERE id ='".$data['idfamiliar']."'";
            
        $resultP = $mysqli->query($queryP);
		$resultD = $mysqli->query($queryD);
        $resultF = $mysqli->query($queryF);
				
	    debugL($queryP,"dependiente");
	    debugL($queryD,"dependiente");
	    debugL($queryF,"dependiente");
	    
        if($resultP == true && $resultD == true && $resultF == true){
            echo notificacion(1,"Dependiente aprobado","");
            exit;    
        }else{
            echo notificacion(2,"Problema al actualizar la validación","");
            exit;
        }
    }
?>