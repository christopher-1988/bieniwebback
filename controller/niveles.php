<?php
    include_once("../config/config.php");
    include_once("funciones.php");
    
    list($METHOD,$PARAMS)=router($_SERVER['REQUEST_METHOD'],$_REQUEST['op']);

    if ($METHOD =='GET'){
        switch($PARAMS){ 
            case 'niveles':
                getNiveles();
                break;
			case 'nivelId':
                getNivelId();
                break;
            default:
                echo "{failure-GET:true}";
                break;
        }
    }
    
    if ($METHOD =='POST') {
        switch($PARAMS){
    	    case 'nivel':
                addNivel();
                break;
    	    default:
                echo "{failure-POST:true}";
                break;
        } 
	}
	
	if ($METHOD =='PUT') {
        switch($PARAMS){ 
			case "nivel" :
    			 editNivel();
    			 break;
    	    default:
                echo "{failure-POST:true}";
                break;
        } 
	}
	
	if ($METHOD =='DELETE') {
        switch($PARAMS){ 
			case "nivel" :
    			 deleteNivel();
    			 break;
    	    default:
                echo "{failure-POST:true}";
                break;
        } 
	} 
    
	function formulario(){
		//$json = file_get_contents("php://input");
		//$_REQUEST = json_decode($json,true);
		
        $data['id']	= (!empty($_REQUEST['id'])?$_REQUEST['id']:'');
        $data['nombre'] = (!empty($_REQUEST['nombre'])?$_REQUEST['nombre']:'');
        $data['descripcion'] = (!empty($_REQUEST['descripcion'])?$_REQUEST['descripcion']:'');
		$data['estatus'] = (!empty($_REQUEST['estatus'])?$_REQUEST['estatus']:'');
		$data['busqueda']=(!empty($_REQUEST['busqueda'])?$_REQUEST['busqueda'] : '');
		$data['page']		= (!empty($_REQUEST['page']) ? $_REQUEST['page'] : 1);
	    
	    return $data;
    }
    
    function params(){
	    $data['id']	= (!empty($_GET['id']) ? $_GET['id'] : '');
	    $data['page'] = (!empty($_REQUEST['page']) ? $_REQUEST['page'] : 1);
	    return $data;
    }
    
    function getNiveles(){
        global $mysqli;
        $data = formulario();
        $response = array();
        
        $query  = " SELECT id,nombre,descripcion,estado 
            FROM `niveles` WHERE estado = 1";
            
        $result = $mysqli->query($query);
		$recordTotals = $result->num_rows;

		if($recordTotals == 0){
            echo response($response,0,0,0);
            exit;  
		}
         
        if($recordTotals > 0){
		    while($row = $result->fetch_assoc()){ 
			    $estado = ($row["estado"] == '1') ? 'Activo' : 'Inactivo';
                $response[] = array(
                    'id'            =>  $row["id"],
                    'nombre'        =>  $row["nombre"],
					'descripcion'   =>  $row["descripcion"],
					'estado'   		=>  $estado);
            }
            
            echo response($response,$recordTotals,0,0);
            exit;
		}           
    }
	
	function getNivelId(){
        global $mysqli;
        //$data = formulario();
        $resultado = array();
		
        $query  = " SELECT `id`, `nombre`, `descripcion`, `estatus` 
					FROM `niveles` WHERE id ='".$_GET['id']."'";
        
        $result = $mysqli->query($query);
        debugL($query,"addpaciente");
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
    
    //-CRUD---------------------------------------------------------------------
    function addNivel(){
        global $mysqli;
        $data = formulario();
        
        $query = "SELECT `id` FROM `niveles` WHERE nombre= '".$data['nombre']."'  ";
        
        $result = $mysqli->query($query);
		$row = $result->num_rows;
		//debugL($query."const","addfavorito");
		if($row > 0){		    
    		echo notificacion(2,"Este nivel ya esta agregado en su lista de niveles","");
            exit;            
    	} else {    	    
    	    $query="INSERT INTO `niveles`(`id`, `nombre`, `descripcion`, `estatus`, `fechacreacion`) VALUES (null,
    	            '".$data['nombre']."',
    	            '".$data['descripcion']."',
					'".$data['estatus']."',
    	            now())";
    	            
    		$result = $mysqli->query($query);
		    //debugL($query."insert","addfavorito");
    		if($result==true){
    			$id = $mysqli->insert_id;
    			echo notificacion(1,"Nivel agregado de forma exitosa",$id);
                exit;
    		}else{
    			echo notificacion(2,"Problema al agregar el nivel","");
                exit;
    		}
    	}
    }
    
    function editNivel(){
		//debugL("editNivel","addpaciente");
        global $mysqli;		
        $data   = formulario();
        
        $query=" UPDATE `niveles` SET
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
                    'msg'       => "Nivel actualizado de forma exitosa "));
            exit;    
        }else{
            echo notificacion(2,"Problema al actualizar el nivel","");
            exit;
        }
    }
	
	function deleteNivel(){
        global $mysqli;
        
        $query="DELETE FROM `niveles` WHERE id='".$_GET['id']."'";
    	$result = $mysqli->query($query);
    	//debugL($query."delet","favorito");
    	if($result==true){
            $id = $mysqli->insert_id;
    	    echo notificacion(1,"Nivel eliminado de forma exitosa","");
                exit;
    	}else{
    	    echo notificacion(2,"Problema al eliminar el nivel","");
            exit;
        }
    }
?>