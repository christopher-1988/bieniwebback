<?php
    include_once("../config/config.php");
    //-REPONSE----------------------------------------------------- 
    function notificacion($rsp,$msg,$id,$item=array()){
        return json_encode(array(
                'rsp'   => $rsp,
                'id'    => $id,
                'msg'   => $msg,
                'item'  => $item));
    }
	
    function response($data,$recordsResults,$recordsFiltered,$current_page){
        return json_encode(array(
                'data'              => $data,
                'recordsTotals'    => $recordsResults,
                'recordsFiltered'   => $recordsFiltered,
                'current_page'      => $current_page));
    }
    
    function router($METHOD,$REQUEST){
        $PARAM  = isset($REQUEST)?$REQUEST:"";
        return array($METHOD,$PARAM);
    }
    //-DIRECTORIO--------------------------------------------------
    function folderCreate($directorio){
        if(file_exists($directorio)){
            return true;
        }else{
            $target_path2 = utf8_decode($directorio);
        	if (!file_exists($target_path2))
        	mkdir($target_path2, 0777, true);
            return true;
        }
    }
    
    function folderDelete($ruta,$fileName){
        if(isset($fileName)){
            if(is_file($ruta)){
                unlink($ruta);
                return $rsp = true;
            }else{
                return $rsp = false;  
            }
        }
    }
    
    function folderCountFile($directorio){
        $total = count(glob($directorio.'{*.JPG,*.jpg,*.png,*.pdf,*.PDF,*.doc,*.DOC,*.docx,*.jpeg,*.JPEG}',GLOB_BRACE));
        return $total;
    }
    
    function deleteDirectory($directorio) {
		if(!$dh = @opendir($directorio)) return;
		    while (false !== ($current = readdir($dh))){
			if($current != '.' && $current != '..') {
				//echo 'Se ha borrado el archivo '.$dir.'/'.$current.'<br/>';
				if (!@unlink($directorio.'/'.$current)) 
					deleteDirectory($directorio.'/'.$current);
			}
		}
		closedir($dh);
		//echo 'Se ha borrado el directorio '.$dir.'<br/>';
		@rmdir($directorio);
	}
    //-CLAVE--------------------------------------------------------
    function cifrarPassword($password){
		return $pass = hash('sha256', (get_magic_quotes_gpc() ? stripslashes($password) : $password));
    }
    
    function generarPassword(){
        $longitud = 6;
        $password = "";
	    $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890.,$#*&()";
	    
	    for($i=0;$i<$longitud;$i++) {
	      $password .= substr($str,rand(0,70),1);
	    }

		$hashed_pass = hash('sha256', (get_magic_quotes_gpc() ? stripslashes($password) : $password));
    }
    //-FORMATO------------------------------------------------------
    function formatoFecha($fechaAsg){
        $actual= date('Y-m-d H:i:s');
    
        $fecha1 = new DateTime($actual);//fecha inicial
        $fecha2 = new DateTime($fechaAsg);//fecha de cierre
        
        $intervalo = $fecha1->diff($fecha2);
        
        return $intervalo->format('%H horas %i minutos %s segundos');    
    }
    //-UTIL--------------------------------------------------------
    function UUID(){
        $uuid=substr(md5(time()), 0, 16);
        return $uuid;
    }
    
    function convertirBase64ToImage ($base64, $ruta, $nombre){
		/*
		$rutaImagenSalida = $ruta."/".$nombre.'.png';
        $imagenBinaria = base64_decode($base64);
        return file_put_contents($rutaImagenSalida, $imagenBinaria);
		*/
		$img = str_replace('data:image/png;base64,', '', $base64);
		$img = str_replace(' ', '+', $img);
		$data = base64_decode($img);
		$rutaImagenSalida = $ruta."/".$nombre.'.png';
		return file_put_contents($rutaImagenSalida, $data);
    }
    //-BD----------------------------------------------------------
    function tokenUser($correo){
        global $mysqli;        
        $query="SELECT id,nivel,usuario,nombre FROM `usuarios` WHERE correo='".$correo."'";
        //debugL($query,"token-usuario");
        $result = $mysqli->query($query);
        $row = $result->fetch_assoc();
        return  array ($row['id'],$row['nivel'],$row['usuario'],$row['nombre']);
    }
    
    function getValor($campo,$tabla,$id){
	    global $mysqli;
	
    	if($id != '' || $id >= '0'){
    		$q = "SELECT $campo FROM $tabla WHERE id IN ($id) LIMIT 1";	
    		$r = $mysqli->query($q);
    		$val = $r->fetch_assoc();
    		//evalua para evitar que un resultado null de error
    		if($val){
    		    $valor = $val[$campo];
    	    }else{
    		    $valor = '';
    	    }
    	}else{
    		$valor = '';
    	}	
    	return $valor;
    }
	//VALIDACIONES
    function validarURL($url){
		$options = array(
		  'options' => array(
			'default' => false, // Valor devuelto si la URL no es válida
			'regexp' => '/^https:\/\//' // Expresión regular para validar el esquema
		  )
		);

		if (filter_var($url, FILTER_VALIDATE_URL, $options)) {
		  return true;
		} else {
		  return false;
		}
	}
?>