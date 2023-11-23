<?php
    include_once("../config/config.php");
    include_once("funciones.php");
    include_once("../core/router.php");

    $router = new Router();

    function formulario(){

      $data['id']	= (!empty($_REQUEST['id']) ? $_REQUEST['id'] : '');

      $data['nombre']	= (!empty($_REQUEST['nombre']) ? $_REQUEST['nombre']:'');

      $data['apellido'] = (!empty($_REQUEST['apellido']) ? $_REQUEST['apellido']:'');

      $data['correo']	= (!empty($_REQUEST['correo']) ? $_REQUEST['correo']:'');

      $data['clave']	= (!empty($_REQUEST['clave']) ? $_REQUEST['clave']:'');

      $data['telefono'] = (!empty($_REQUEST['telefono']) ? $_REQUEST['telefono']:'');

      $data['busqueda'] = (!empty($_REQUEST['busqueda']) ? $_REQUEST['busqueda'] : '');
      
      $data['fecha'] = date('Y/m/d');

      $data['datetime'] = date("Y-m-d H:i:s");

      return $data;
    }
    
    $router->get('usuarios',function(){
      global $mysqli;
      $response = array();
      try{
        
          $query  = " SELECT u.id AS idusuario,p.id AS idpersonal,CONCAT(p.nombre,' ',p.apellido) AS nombre,u.correo,p.imagen,u.estado
            FROM usuarios u 
            INNER JOIN personal p ON p.idusuario=u.id
            WHERE u.estado = 'activo'";
                    
          $result  = $mysqli->query($query);

		      $recordsResults = $result->num_rows;

          if($recordsResults == 0){
            echo response($response,0,0,0);
            exit; 
          }
        
          while($row = $result->fetch_assoc()){  
            $response[] = array(
              'id'            => $row["idusuario"],
              'idpersonal' => $row["idpersonal"],
              'nombre'     => $row["nombre"],
              'correo'       => $row["correo"],
					    'imagen'   	 => $row["imagen"],
					    'estado'   		=> $row["estado"]);
        }
        
        echo response($response,$recordsResults,0,0);
      }catch(Exception $e) {
        die($e);
      }
    });

    $router->get('usuario',function($params){
      global $mysqli;
      $response = array();
      try{

          $query = " SELECT u.id,p.nombre,p.apellido,u.correo,p.imagen,u.estado
            FROM usuarios u 
            INNER JOIN personal p ON p.idusuario=u.id
            WHERE u.id = '".$params['id']."' 
            AND u.estado = 'activo'";
                    
          $result  = $mysqli->query($query);
		      
          $recordsResults = $result->num_rows;
          
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
      }catch(Exception $e) {
        die($e);
      }
    });
    
    $router->post('usuario',function($params){
      global $mysqli;
      $form = formulario();
      try{
          
          $query = "SELECT `id` 
            FROM `usuarios` 
            WHERE correo ='".$params['correo']."'";
          
          $result = $mysqli->query($query);
          
          $row = $result->num_rows;
          
          if($row > 0){		    
            echo notificacion(2,"Este usuario ya existe","");
            exit;            
          }

          $query = " INSERT INTO `usuarios`(`id`, `correo`, `clave`, `telefono`, `nivel`, `condiciones`, `verificacion`, `verificacioncorreo`, `estado`, `fecha`) 
            VALUES (null,
            '".$params['correo']."',
            '".$params['clave']."',
            '".$params['telefono']."',
            '2',
            'si',
            'si',
            'si',
            'activo',
            '".$form['datetime']."')";
    		
        $result = $mysqli->query($query);
		    if($result==true){
    	    $id = $mysqli->insert_id;
    	    $imagen = "";
    	        
    		  $queryPers = "INSERT INTO `personal`(`id`, `idusuario`, `nombre`, `apellido`, `imagen`, `creado`) 
    		        VALUES (null,
    		        '".$id."',
    		        '".$params['nombre']."',
    		        '".$params['apellido']."',
    		        '".$imagen."',
    		        '".$params['datetime']."')";
    		    
    		    $result = $mysqli->query($queryPers);
		        
            if($result==true){
    		      $idP = $mysqli->insert_id;
    		      echo notificacion(1,"Error al crear el personal",$idP);
              exit;
    		    }else{
              echo notificacion(2,"Error al crear el personal","");
              exit;
    		    }
    		}else{
    			echo notificacion(2,"Problema al agregar el usuario","");
          exit;
    		}
      }catch(Exception $e) {
        die($e);
      }
    });
    
    $router->post('editar',function($params){
      global $mysqli;

      try{
            $query=" UPDATE `afiliados` SET
              `nombre`='".$params['nombre']."',
              `descripcion`='".$params['descripcion']."',
              `estatus`='".$params['estatus']."'
              WHERE `id` = '".$params['id']."'";
            
            $result = $mysqli->query($query);

            if($result == true){
              echo notificacion(1,"Usuario editado","");
              exit;    
            }else{
                echo notificacion(2,"Problema al actualizar","");
                exit;
            }
      }catch(Exception $e) {
        die($e);
      }
    });

    $router->post('eliminar',function($params){
      global $mysqli;

      try{
            $query = " DELETE FROM `usuarios` 
            WHERE id = '".$params['id']."'";

    	      $result = $mysqli->query($query);
          	if($result==true){
    	        //"DELETE FROM `personal` WHERE idusuario = '".$params['id']."'";
    	        echo notificacion(1,"Usuario eliminado de forma exitosa","");
              exit;
    	      }else{
    	        echo notificacion(2,"Usuario al eliminar el afiliado","");
              exit;
            }
      }catch(Exception $e) {
        die($e);
      }
    });

    $router->run();

?>