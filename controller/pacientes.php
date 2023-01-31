<?php
  include_once("../config/config.php");
  include_once("../model/router.php");
  include_once("funciones.php");
	
  $router = new Router();
    
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
  //-FUNCIONAL-------------------------------------------
  function estados($idestado,$idestadoverificacion,$estado){
    /*
		ESTADOS PACIENTE   | ESTADOS DOCUMENTO VERIFICACION
		1-activo           | 1-aprobado         
		2-inactivo         | 2-no aprobado
		3-documento enviado| 3-en espera de aprobacion
		*/
    $estado = "";
    if($idestado == 1 && $idestadoverificacion == 1){
      $estado = "Aprobado";
      return array(1,$estado); 
    }

    if($idestado == 2 && $idestadoverificacion == 2){
      $estado = "No aprobado";
      return array(2,$estado);
    }

    if($idestado == 3 && $idestadoverificacion == 3){
      $estado = "Documento enviado";
      return array(3,$estado);
    }
  }
    
  function tipoVerificacion($tipoVerificacion,$imagenDocumento,$imagenVerificacion){
    /*
		ESTADOS VEREFICACION
		-verificacion-automatica  
		-verificacion-manual
		*/
    $estado = "";
    debugL($tipoVerificacion."-".$imagenDocumento."-".$imagenVerificacion,"tipo-verificacion");
        
    if($tipoVerificacion == "") {
      $estado = "Error guardado verificación";
    }
    
    if ($tipoVerificacion == "verificacion-automatica") {
      
      if ($imagenDocumento == "" || $imagenVerificacion == "") {
        $error="";

        if($imagenDocumento == ""){
          $error="documento";
        }
        
        if($imagenVerificacion == ""){
          $error="verificacion";
        }

        if($imagenDocumento == "" && $imagenVerificacion == ""){
          $error="ambos";
        }
        
        $estado = "Error en guardado de imagen ".$error;
        
      } else {
        $estado = ucfirst(str_replace('verificacion-','',$tipoVerificacion));
      }
    }
    
    if ($tipoVerificacion == "verificacion-manual") {
      if ($imagenDocumento == "" || $imagenVerificacion == "") {
            
        if($imagenDocumento == ""){
          $error="documento";
        }
        
        if($imagenVerificacion == ""){
          $error="verificacion";
        }

        if($imagenDocumento == "" && $imagenVerificacion == ""){
          $error="ambos";
        }
        
        $estado = "Error en guardado de imagen ".$error;
        
      }else {
          $estado = ucfirst(str_replace('verificacion-','',$tipoVerificacion));
      }
    }
    return $estado;
  }
  //-ROUTER-------------------------------------------------------
  $router->get('pacientes',function(){
    global $mysqli;
    $data = params();
		$response = array();
       
    $query  = " SELECT p.idusuario AS idusuario,p.id AS idpaciente,CONCAT(p.nombre,' ',p.apellido) AS nombre,p.edad,IF(p.idparentesco = 0,'Principal',pr.nombre) AS perfil, p.fechanacimiento,tp.nombre AS tipodocumento,pd.documento,u.telefono,
            p.idestado,pd.idestadoverificacion,pd.tipoverificacion,pd.imagen_documento,pd.imagen_verificacion,pd.estado
            FROM pacientes p
            INNER JOIN usuarios u ON u.id=p.idusuario
            LEFT JOIN pacientes_documentos pd ON pd.idpaciente=p.id
            LEFT JOIN tipos_documento tp ON tp.id=pd.idtipodocumento
            LEFT JOIN parentescos pr ON pr.id=p.idparentesco
            WHERE 1 = 1 ";
		
		if(!$result = $mysqli->query($query)){
    		die($mysqli->error);  
    	}
    	
    $recordsTotals = $result->num_rows;
		$inicio  = $data['page'] * 10 - 10;   
    $query  .= " ORDER BY p.id DESC LIMIT $inicio, 10 ";
    $result  = $mysqli->query($query);
    $recordsFiltered = $result->num_rows;
    //debugL($query,"getValidaciones");
		if($recordsTotals == 0){
			echo response($response,0,0,0);
		}   
		
		if($recordsTotals > 0){
			while($row = $result->fetch_assoc()){ 
			    
			  list($idestado,$estado) = estados($row['idestado'],$row['idestadoverificacion'],$row['estado']);
			    
			  $tipoVerificacion = tipoVerificacion($row["tipoverificacion"],$row["imagen_documento"],$row["imagen_verificacion"]);
			    
		    $response[] = array(
          'idusuario'     => $row['idusuario'],
          'idpaciente'   => $row['idpaciente'],
          'nombre'       => ucwords($row['nombre']),
          'edad'            => $row['edad'],
          'perfil'            => $row['perfil'], 
          'fechanacimiento'=> $row['fechanacimiento'],
          'tipodocumento'  => $row['tipodocumento'],
          'documento'         => $row['documento'],
          'telefono'              => $row['telefono'],
          'tipoverificacion'  => $tipoVerificacion,
          'idestado'             => $idestado,
          'estadoRegistro'   => $tipoVerificacion,
          'estado'                => $estado);
			}
			echo response($response,$recordsTotals,$recordsFiltered,0);
		} 
  });
	
	$router->get('pacienteId',function(){
    global $mysqli;
    $data = params();
		$response = array();
       
    $query  = " SELECT p.id,p.idparentesco,tp.nombre AS tipodocumento,pd.documento,p.nombre, p.apellido,p.edad, p.fechanacimiento, p.gruposangre,p.numeroemergencia,p.imagen, p.discapacidad,pd.imagen_documento,IF(u.verificacioncorreo=0,'no','si') AS verificacioncorreo,u.telefono,pd.tipoverificacion
            FROM pacientes p
            INNER JOIN usuarios u ON u.id=p.idusuario
            LEFT JOIN pacientes_documentos pd ON pd.idpaciente=p.id
            LEFT JOIN tipos_documento tp ON tp.id=pd.idtipodocumento
            WHERE p.id='".$data['id']."' AND pd.estado='activo ";
		
		if(!$result = $mysqli->query($query)){
    	die($mysqli->error);  
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
    				'tipoverificacion'  => ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
    				'imagendocumento'    => $row['imagen_documento'],
    				'verificacioncorreo' => $row['verificacioncorreo']);
			}
			echo response($response,$recordsTotals,0,0);
		}        
  });
?>