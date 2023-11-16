<?php
  include_once("../config/config.php");
  include_once("funciones.php");
  include_once("../core/router.php");
	
  $router = new Router();
    
  function formulario(){

      
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
  //-FUNCIONAL
  function estados($idestado,$idestadoverificacion,$estado){
    /*
		ESTADOS PACIENTE | ESTADOS DOCUMENTO VERIFICACION
		1-activo                    | 1-aprobado         
		2-inactivo                 | 2-no aprobado
		3-documento enviado | 3-en espera de aprobacion
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
  
  $router->get('pacientes',function($params){
    global $mysqli;

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
		
    $inicio  = $params['page'] * 10 - 10;   
    
    $query  .= " ORDER BY p.id DESC LIMIT $inicio, 10 ";
    $result  = $mysqli->query($query);
    
    $recordsFiltered = $result->num_rows;
    
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
	
	$router->get('paciente',function($params){
    global $mysqli;
		$response = array();
       
    $query  = " SELECT p.id,p.idparentesco,tp.nombre AS tipodocumento,pd.documento,p.nombre, p.apellido,p.edad, p.fechanacimiento, p.gruposangre,p.numeroemergencia,p.imagen, p.discapacidad,pd.imagen_documento,IF(u.verificacioncorreo=0,'no','si') AS verificacioncorreo,u.telefono,pd.tipoverificacion
            FROM pacientes p
            INNER JOIN usuarios u ON u.id=p.idusuario
            LEFT JOIN pacientes_documentos pd ON pd.idpaciente=p.id
            LEFT JOIN tipos_documento tp ON tp.id=pd.idtipodocumento
            WHERE p.id='".$params['id']."' 
            AND pd.estado='activo ";
		
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
  //Dependientes
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

  $router->get('dependientes',function($params){
    global $mysqli;
    $response = array();

    try{
            
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
          echo response($response,0,0,0);
          exit;       
        }   
          
        if($records > 0){
          while($row=$result->fetch_assoc()){        
            $response[] = array(
              'idusuario'     => $row['idusuario'],
              'idpaciente'    => $row["idpaciente"],
              'iddocumento'   => $row["iddocumento"],
              'idfamiliar'    => $row["idfamiliar"],
              'principal'=> nombrePaciente($row["idprincipal"]),
              'nombre' => ucwords($row["nombre"]),
              'parentesco' => $row["parentesco"],
              'tipodocumento'=>$row["tipodocumento"],
              'documento'     => $row["documento"],
              'tipoverificacion'=>ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
              'estadodocumento'=>nombreEstado($row["estadodocumento"]),
              'estadofamiliar'=>nombreEstado($row["estadofamiliar"]));
            }
            echo response($response,$records,0,0);
          }
    }catch(Exception $e) {
      die($e);
    }
  });

  $router->get('dependiente',function($params){
    global $mysqli;
    $response = array();
    
    try{
        
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
                AND p.id ='".$params['id']."' ";
					
        $result  = $mysqli->query($query);

		    $records = $result->num_rows;

		    if($records == 0){
          echo response($response,0,0,0);
          exit;       
        }   
       
		    if($records > 0){
			    while($row = $result->fetch_assoc()){        
				    $response[] = array(
				      'idpaciente'    => $row["idpaciente"],
              'iddocumento'   => $row["iddocumento"],
              'idfamiliar'    => $row["idfamiliar"],
              'principal'=>nombrePaciente($row["idprincipal"]),
              'nombre' => ucwords($row["nombre"]),
              'parentesco'    => $row["parentesco"],
              'tipodocumento' => $row["tipodocumento"],
              'documento' => $row["documento"],
              'tipoverificacion'  => ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
              'estadodocumento'   => nombreEstado($row["estadodocumento"]),
              'estadofamiliar'    => nombreEstado($row["estadofamiliar"]));
            }
            
            echo response($response,$records,0,0);
          
		      }        
    }catch(Exception $e) {
      die($e);
    }
  });

  $router->post('dependiente/aprobar',function($params){
    global $mysqli;
    $data = formulario();

    try {

        $mysqli->begin_transaction();
        //Paciente
        $queryP = "UPDATE pacientes SET idestado = 1 WHERE id = ?";

        $resultP = $mysqli->prepare($queryP);

        $resultP->bind_param("s", $data['idpaciente']);

        $resultP->execute();
        //Paciente
        $queryD = "UPDATE pacientes_documentos SET idestadoverificacion = 1, estado = 'activo' WHERE id = ?";

        $resultD = $mysqli->prepare($queryD);

        $resultD->bind_param("s", $data['iddocumento']);

        $resultD->execute();
        //Relacion familiar
        $queryF = "UPDATE relaciones_familiares SET idestadoverificacion = 1 
        WHERE id = ?";

        $resultF = $mysqli->prepare($queryF);

        $resultF->bind_param("s", $data['idfamiliar']);

        $resultF->execute();

        $mysqli->commit();

        echo notificacion(1, "Dependiente aprobado", "");
    } catch (Exception $e) {
      $mysqli->rollback();
      echo notificacion(2, "Problema al actualizar la validación", $e->getMessage());
    }
  });

  $router->post('dependiente/rechazar',function($params){
    global $mysqli;
    $data = formulario();

    try {

        $mysqli->begin_transaction();
        //Paciente
        $queryP = " UPDATE pacientes SET idestado = 2 WHERE id = ?";

        $resultP = $mysqli->prepare($queryP);

        $resultP->bind_param("i", $data['idpaciente']);

        $resultP->execute();
        //Documento
        $queryD = " UPDATE pacientes_documentos SET idestadoverificacion = 2, estado =  'inactivo'
        WHERE id = ?";
        
        $resultD = $mysqli->prepare($queryD);
        
        $resultD->bind_param("i", $data['iddocumento']);

        $resultD->execute();
        //Relacion familiar
        $queryF = " UPDATE relaciones_familiares SET idestadoverificacion = 2 
        WHERE id = ?";

        $resultF = $mysqli->prepare($queryF);

        $resultF->bind_param("i", $data['idfamiliar']);

        $resultF->execute();

        $mysqli->commit();

        echo notificacion(1, "Dependiente rechazado", "");
    } catch (Exception $e) {
      $mysqli->rollback();
      echo notificacion(2, "Problema al actualizar la validación", $e->getMessage());
    }
  });

  $router->get('principales',function($params){
    global $mysqli;
		$response = array();
    
    try{
          
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
					
      $stmt = $mysqli->prepare($query);

      //$stmt->bind_param("s", $params['id']);

      if (!$stmt->execute()) {
        throw new Exception("Error execute cnst: " . $stmt->error); 
      }
    
      $result = $stmt->get_result();
      
      $records = $result->num_rows;

      if ($records == 0) {
        echo response($response, 0, 0, 0);
        exit;
      }

		  if($records > 0){
        while($row = $result->fetch_assoc()){        
				  $documento = $row['imagen_documento']===""
          ?""
          :"";

          $verificacion = $row['imagen_verificacion']===""
          ?""
          :"";
        
        "asset/perfiles/".$row['idusuario']."/reconocimientos/".$row['idpaciente'];
          
          $resultado[] = array(
					'idusuario'     => $row['idusuario'],
        	'idpaciente'    => $row['idpaciente'],
        	'iddocumento'   => $row['iddocumento'],
        	'nombre'        => ucwords($row["nombre"]),
        	'tipodocumento' => $row['tipodocumento'],
        	'documento'     => $row['documento'],
          'tipoverificacion'  =>  ucfirst(str_replace('verificacion-','',$row["tipoverificacion"])),
        	'imagen_documento'=> $documento,
        	'imagen_verificacion'=> $verificacion,
        	'estado' => $row['estado']);
			  }
			  echo json_encode($resultado);
		  } 
    }catch(Exception $e) {
      die($e);
    }
  });


  $router->post('principal/aprobar',function($params){
    global $mysqli;
    $data = formulario();

    try {

        $mysqli->begin_transaction();
        //usuario
        $queryU = " UPDATE usuarios SET 
			    estado = 'activo'
			    WHERE id =?";

        $resultU = $mysqli->prepare($queryU);

        $resultU->bind_param("i",$data['idusuario']);

        $resultU->execute();
        //Paciente
        $queryP = " UPDATE pacientes SET 
			    idestado = 1 
			    WHERE  id =?";

        $resultD = $mysqli->prepare($queryP);

        $resultD->bind_param("i", $data['idpaciente']);

        $resultD->execute();
        //Documento
        $queryD = " UPDATE pacientes_documentos SET
			    idestadoverificacion = 1,
			    estado ='activo'
			    WHERE id = ?";
        
        $resultD = $mysqli->prepare($queryD);
        
        $resultD->bind_param("i",$data['iddocumento']);

        $resultD->execute();

        $mysqli->commit();

        echo notificacion(1, "Principal aprobado", "");

    } catch (Exception $e) {
      $mysqli->rollback();
      echo notificacion(2, "Problema al actualizar la validación", $e->getMessage());
    }
  });

  $router->post('principal/rechazar',function($params){
     global $mysqli;
    $data = formulario();

    try {

        $mysqli->begin_transaction();
        //usuario
        $queryU = " UPDATE usuarios SET 
			    estado = 'inactivo'
			    WHERE id =?";

        $resultU = $mysqli->prepare($queryU);

        $resultU->bind_param("i",$data['idusuario']);

        $resultU->execute();
        //Paciente
        $queryP = " UPDATE pacientes SET 
			    idestado = 2 
			    WHERE  id =?";

        $resultD = $mysqli->prepare($queryP);

        $resultD->bind_param("i", $data['idpaciente']);

        $resultD->execute();
        //Documento
        $queryD = " UPDATE pacientes_documentos SET
			    idestadoverificacion = 2,
			    estado ='inactivo'
			    WHERE id = ?";
        
        $resultD = $mysqli->prepare($queryD);
        
        $resultD->bind_param("i",$data['iddocumento']);

        $resultD->execute();

        $mysqli->commit();

        echo notificacion(1, "Principal rechazado", "");

    } catch (Exception $e) {
      $mysqli->rollback();
      echo notificacion(2, "Problema al actualizar la validación", $e->getMessage());
    }
  });
  
  $router->run()
?>