<?php
    //CROSS-REEFRENCE PERMITIDOS
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('content-type: application/json; charset=utf-8');
    //-LIBRERIAS-----------------------------------------------
    //include('librerias/phpqrcode/qrlib.php');
    //-CONEXION------------------------------------------------
    $mysqli = new mysqli("127.0.0.1", "root","M4X14W3B", "bieni");
    
    if ($mysqli->connect_error) {
    	echo "Fallo al conectar a MySQL: (" . $mysqli->connect_error . ") " . $mysqli->connect_error;
    }
    
    $mysqli->query("SET NAMES utf8");
    $mysqli->query("SET CHARACTER SET utf8");

    date_default_timezone_set("America/Panama");
    //DEBUG-----------------------------------------------------
	function debug($txt) {
		$f = fopen("debug.txt", "w"); 
		fwrite($f, $txt);
		fclose($f);
	}

	function debugL($txt,$fileName='debugL') {
		$fileName.='.txt';
		$f = fopen($fileName, "a"); 
		fwrite($f, $txt.PHP_EOL);
		fclose($f);
	}
	
    function debugJ($entrada,$fileName='debugJ') {
		$json_string = json_encode($entrada);
		$file = $fileName.='.json'; 
		//file_put_contents($file, $json_string); 
		$f = fopen($file, "a"); 
		fwrite($f, $json_string.PHP_EOL);
		fclose($f);
    }

?>