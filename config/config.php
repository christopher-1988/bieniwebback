<?php

  $mysqli = new mysqli("localhost", "root", "", "bieni_app_des");
    
  if ($mysqli->connect_error) {
    echo "Fallo al conectar a MySQL: (" . $mysqli->connect_error . ") " . $mysqli->connect_error;
  }
    
  $mysqli->query("SET NAMES utf8");
  
  $mysqli->query("SET CHARACTER SET utf8");

  date_default_timezone_set("America/Panama");
  //DEBUG
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