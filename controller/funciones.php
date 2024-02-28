<?php
    include_once("../config/config.php");

    function notificacion($rsp,$msg,$id,$data=array()){
      return json_encode(array(
        'responseCode'   => $rsp,
        'msg'   => $msg,
        'id'    => $id,
        'item'  => $data));
    }
	
    function response($data,$recordsResults,$recordsFiltered,$current_page){
        return json_encode(array(
                'data'              => $data,
                'recordsTotals'    => $recordsResults,
                'recordsFiltered'   => $recordsFiltered,
                'currentPage'      => $current_page));
    }

    function handleException($e) {
      http_response_code(500);

      $errorMessage = $e->getMessage();

      echo "Estamos presantando problemas ->".$errorMessage;
    }
    //-DIRECTORIO
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
    //-CLAVE
    function cifrarPassword($data){
      $hashed_pass = hash('sha256', stripslashes($data));
      return $hashed_pass;
    }
    //-UTIL
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
	    //VALIDACIONES
    function validarURL($url){
		  $options = array(
		    'options' => array(
			    'default' => false,
			    'regexp' => '/^https:\/\//')
		  );

      if (filter_var($url, FILTER_VALIDATE_URL, $options)) {
        return true;
      } else {
        return false;
      }
	  }
?>