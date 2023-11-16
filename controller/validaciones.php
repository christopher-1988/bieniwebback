<?php
  include_once("../config/config.php");
  include_once("funciones.php");
	
  list($METHOD,$PARAMS)=router($_SERVER['REQUEST_METHOD'],$_REQUEST['op']);
  debugL($METHOD,"mt");
  if ($METHOD == 'GET'){
    switch($PARAMS){
			case 'cuentas':
				cuentasValidar();
				break;
			case 'dependientes':
				dependientesValidar();
				break;
      default:
        echo "{failure-GET:true}";
        break;
    }
  }elseif ($METHOD =='POST') {
    switch($PARAMS){
    	case "cuentaAprobar" :
    		cuentaAprobar();
    		break;
			case "cuentaRechazar" :
    		cuentaRechazar();
    		break;
			case "dependienteAprobar":
				dependienteAprobar();
				break;
			case "dependienteRechazar":
				dependienteRechazar();
				break;
    	default:
        echo "{failure-POST:true}";
        break;
    } 
	}
    
	function formulario(){
    /*
    $data = json_decode(file_get_contents("php://input"), true);
		$JSON 		= file_get_contents("php://input");
    */
		
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
		$data['id']	= (!empty($_GET['id']) ? $_GET['id'] : '');

    $data['page'] = (!empty($_REQUEST['page']) ? $_REQUEST['page'] : 1);
        
	  return $data;
  }
  //-FUNCIONAL
  function nombreEstado($id){
    global $mysqli;
        
    $query = "SELECT `id`, `nombre` 
            FROM `estados_documento_verificacion` 
            WHERE id ='".$id."'";
        
    $result  = $mysqli->query($query);

		$records = $result->num_rows;
    //debugL($query,"nombreEstados");
		if($records == 0){
			return "N/A";
		}   
		
		if($records > 0){
			$row = $result->fetch_assoc();        
			return $row["nombre"];
		}
  }
    
  function nombrePaciente($id){
    global $mysqli;
        
    $query = "SELECT  CONCAT(nombre,' ',apellido) AS nombre 
      FROM `pacientes` 
      WHERE id ='".$id."'";
        
    $result  = $mysqli->query($query);

		$records = $result->num_rows;

		if($records == 0){
			return "N/A";
		}   
		
		if($records > 0){
			$row = $result->fetch_assoc();        
			return $row["nombre"];
		}
  }
  //-VALIDADCION-DEPENDIENTE
  function dependientesValidar(){
    global $mysqli;
		$resultado = array();
       
    $query = " SELECT p.idusuario AS idusuario,p.id AS idpaciente,pd.id AS iddocumento,rf.id AS idfamiliar,rf.idpaciente AS idprincipal,CONCAT(p.nombre,' ',p.apellido) AS nombre,pr.nombre AS parentesco,td.nombre AS tipodocumento,pd.documento,pd.tipoverificacion,pd.idestadoverificacion AS estadodocumento,rf.idestadoverificacion AS estadofamiliar
			FROM  pacientes p 
			INNER JOIN pacientes_documentos pd ON pd.idpaciente= p.id
			INNER JOIN relaciones_familiares rf ON rf.idfamiliar = p.id
            INNER JOIN parentescos pr ON pr.id = p.idparentesco
            INNER JOIN tipos_documento td ON td.id=pd.idtipodocumento
			WHERE p.idparentesco != 0
            AND p.idestado=3 
			AND pd.idestadoverificacion = 3
			AND pd.estado='inactivo'
            AND rf.idestadoverificacion = 3";
					
    $result  = $mysqli->query($query);

		$records = $result->num_rows;
    
		if($records == 0){
			echo json_encode($resultado);
		}   
		
		if($records > 0){
			while($row = $result->fetch_assoc()){        
				$resultado[] = array(
				  'idusuario'     => $row['idusuario'],
				  'idpaciente'    => $row["idpaciente"],
        	'iddocumento'   => $row["iddocumento"],
        	'idfamiliar'    => $row["idfamiliar"],
        	'principal'     => nombrePaciente($row["idprincipal"]),
        	'nombre'        => ucwords($row["nombre"]),
        	'parentesco'    => $row["parentesco"],
        	'tipodocumento' => $row["tipodocumento"],
        	'documento'     => $row["documento"],
        	'tipoverificacion'  => ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
        	'estadodocumento'   => nombreEstado($row["estadodocumento"]),
        	'estadofamiliar'    => nombreEstado($row["estadofamiliar"]));
			}
			echo json_encode($resultado);
		}        
  }
	
	function dependienteValidarId(){
    global $mysqli;
    $data = params();
		$resultado = array();
       
    $query  = " SELECT p.id AS idpaciente,pd.id AS iddocumento,CONCAT(p.nombre,' ',p.apellido) AS nombre,pr.nombre,td.nombre AS tipodocumento,pd.documento,pd.tipoverificacion,sdv.nombre estado
			FROM  pacientes p 
			INNER JOIN pacientes_documentos pd ON pd.idpaciente= p.id
			INNER JOIN relaciones_familiares rf ON rf.idfamiliar = p.id
            INNER JOIN parentescos pr ON pr.id = p.idparentesco
            INNER JOIN tipos_documento td ON td.id=pd.idtipodocumento
			INNER JOIN estados_documento_verificacion sdv ON sdv.id=pd.idestadoverificacion
			WHERE p.idparentesco != 0
            AND p.idestado=3 
			AND pd.idestadoverificacion = 3
			AND pd.estado='inactivo'
            AND p.id ='".$data['id']."' ";
					
    $result  = $mysqli->query($query);

		$records = $result->num_rows;

		if($records == 0){
			echo json_encode($resultado);
		}   
		
		if($records > 0){
			while($row = $result->fetch_assoc()){        
				$resultado[] = array(
				'idpaciente'    => $row["idpaciente"],
        'iddocumento'   => $row["iddocumento"],
        'idfamiliar'    => $row["idfamiliar"],
        'principal'     => nombrePaciente($row["idprincipal"]),
        'nombre'        => ucwords($row["nombre"]),
        'parentesco'    => $row["parentesco"],
        'tipodocumento' => $row["tipodocumento"],
        'documento'     => $row["documento"],
        'tipoverificacion'  => ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
        'estadodocumento'   => nombreEstado($row["estadodocumento"]),
        'estadofamiliar'    => nombreEstado($row["estadofamiliar"]));
			}
			echo json_encode($resultado);
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
	    
    if($resultP == true && $resultD == true && $resultF == true){
      echo notificacion(1,"Dependiente aprobado","");
      exit;    
    }else{
      echo notificacion(2,"Problema al actualizar la validación","");
      exit;
    }
  }
  //-VALIDADCION-CUENTA
	function cuentasValidar(){
    global $mysqli;
		$resultado = array();
       
    $query  = " SELECT u.id AS idusuario,p.id AS idpaciente,pd.id AS iddocumento,CONCAT(p.nombre,'',p.apellido) AS nombre,td.nombre AS tipodocumento,pd.documento,pd.tipoverificacion,pd.imagen_documento,pd.imagen_verificacion,sdv.nombre estado
			FROM usuarios u
			INNER JOIN pacientes p ON p.idusuario=u.id
			INNER JOIN pacientes_documentos pd ON pd.idpaciente= p.id
			INNER JOIN tipos_documento td ON td.id=pd.idtipodocumento
			INNER JOIN estados_documento_verificacion sdv ON sdv.id=pd.idestadoverificacion
			WHERE u.estado='inactivo' 
			AND p.idparentesco = 0 
			AND p.idestado=3 
			AND pd.idestadoverificacion = 3
			AND pd.estado='inactivo' ";
					
    $result  = $mysqli->query($query);
		$records = $result->num_rows;

		if($records == 0){
			echo json_encode($resultado);
		}   
		
		if($records > 0){
			while($row = $result->fetch_assoc()){        
				$resultado[] = array(
					'idusuario'     => $row['idusuario'],
        	'idpaciente'    => $row['idpaciente'],
        	'iddocumento'   => $row['iddocumento'],
        	'nombre'        => ucwords($row["nombre"]),
        	'tipodocumento' => $row['tipodocumento'],
        	'documento'     => $row['documento'],
          'tipoverificacion'  =>  ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
        	'imagen_documento'   => $row['imagen_documento'],
        	'imagen_verificacion'=> $row['imagen_verificacion'],
        	'estado'             => $row['estado']
				);
			}
			echo json_encode($resultado);
		}        
  }

	function cuentaAprobar(){
    global $mysqli;		
    $data   = formulario();
		/*
		ESTADOS USUARIO
		1-activo
		2-inactivo
		*/
		$queryU = " UPDATE usuarios SET 
			estado = 'activo'
			WHERE id ='".$data['idusuario']."'";
		/*
		ESTADOS PACIENTE
		1-activo
		2-inactivo
		3-documento enviado
		*/
		$queryP = " UPDATE pacientes SET 
			idestado = 1 
			WHERE  id ='".$data['idpaciente']."'";
		/*
		ESTADOS DOCUMENTO VERIFICACION
		1-aprobado
		2-no aprobado
		3-en espera de aprobacion
		*/	
    $queryD = " UPDATE pacientes_documentos SET
			idestadoverificacion = 1,
			estado ='activo'
			WHERE id = '".$data['iddocumento']."'";
            
    $resultU = $mysqli->query($queryU);
		$resultP = $mysqli->query($queryP);
		$resultD = $mysqli->query($queryD);
		

    if($resultU == true && $resultP ==true && $resultD =true){
      echo notificacion(1,"Validación aprobada","");
      exit;    
    }else{
      echo notificacion(2,"Problema al actualizar la validación","");
      exit;
    }
  }
	
	function cuentaRechazar(){
    global $mysqli;		
    $data   = formulario();
		/*
		ESTADOS USUARIO
		1-activo
		2-inactivo
		*/
		$queryU = " UPDATE usuarios SET 
			estado='inactivo'
			WHERE id ='".$data['idusuario']."'";
		/*
		ESTADOS PACIENTE
		1-activo
		2-inactivo
		3-documento enviado
		*/
		$queryP = " UPDATE pacientes SET 
			idestado= 2
			WHERE id ='".$data['idpaciente']."'";
		/*
		ESTADOS DOCUMENTO VERIFICACION
		1-aprobado
		2-no aprobado
		3-en espera de aprobacion
		*/	
    $queryD = " UPDATE pacientes_documentos SET
			idestadoverificacion = 2,
			estado ='inactivo'
			WHERE id = '".$data['iddocumento']."'";
            
    $resultU = $mysqli->query($queryU);
		$resultP = $mysqli->query($queryP);
		$resultD = $mysqli->query($queryD);

	  if($resultU == true && $resultP ==true && $resultD =true){
      echo notificacion(1,"Proceso completado de forma exitosa", $data['id']);
      exit;    
    }else{
      echo notificacion(2,"Problema al actualizar la validación","");
      exit;
    }
  }
	//-OTROS
  function getValidaciones(){
    global $mysqli;
		$resultado = array();
       
    $query  = " SELECT a.id, CONCAT(b.nombre,' ',b.apellido) AS nombre, c.nombre AS tipodocumento, a.documento, 
					d.nombre AS estadoverificacion, a.tipoverificacion, a.imagen_documento, a.imagen_verificacion, 
					a.estado, b.idusuario, a.idpaciente
					FROM `pacientes_documentos` a
					LEFT JOIN pacientes b ON b.id = a.idpaciente
					LEFT JOIN tipos_documento c ON c.id = a.idtipodocumento
					LEFT JOIN estados_documento_verificacion d ON d.id = a.idestadoverificacion
					WHERE a.estado = 'inactivo' OR a.estado = 'activo' ";
					
    $result  = $mysqli->query($query);
		$records = $result->num_rows;

		if($records == 0){
			echo json_encode($resultado);
		}   
		
		if($records > 0){
			while($row = $result->fetch_assoc()){        
				$resultado[] = array(
					'id'            	=>  $row["id"],
					'nombre'        	=>  ucwords($row["nombre"]),
					'tipodocumento'   	=>  $row["tipodocumento"],
					'documento'   		=>  $row["documento"],
					'estadoverificacion'=>  $row["estadoverificacion"],
					'tipoverificacion'  =>  ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
					'imagen_documento'  =>  $row["imagen_documento"],
					'imagen_verificacion'=> $row["imagen_verificacion"],
					'estado'   			=>  $row["estado"],
					'idusuario'  		=>  $row["idusuario"],
					'idpaciente'  		=>  $row["idpaciente"],
				);
			}
			echo json_encode($resultado);
		}        
  }
	
	function getValidacionId(){
    global $mysqli;
    $params    = params();
		$resultado = array();
		
    $query  = " SELECT `id`, `nombre`, `descripcion`, `estatus` 
			FROM `pacientes_documentos` WHERE id ='".$params['id']."'";
        
    $result  = $mysqli->query($query);
    $records = $result->num_rows;

		if($records == 0){
			echo json_encode($resultado);
		}   
		
		if($records > 0){
			while($row = $result->fetch_assoc()){
				$resultado = array(
					'id'            =>  $row["id"],
					'nombre'        =>  $row["nombre"],
					'descripcion'   =>  $row["descripcion"],
					'estatus'     	=>  $row["estatus"]
					);
			}
      echo json_encode($resultado);
		}
  }
?>