<?php
include_once("../config/config.php");
/**
*
* CUSTON ROUTER
* 
**/
class Router {
    
    function __construct(){}
    /**
    * 
    * @var $router string de el enrutador para manejar
    * @var funcion callback que se dispara al cumplir la condicion
    *
    **/
    public function get($ruta,$callback=null){
        if($_SERVER['REQUEST_METHOD']!=='GET'){
            return false;
        }  
        
        $URI = $_SERVER['REQUEST_URI'];
        $OP  = $_REQUEST['op'];
     
        /*if($OP !== $ruta){
            echo "{failure-GET}";
        }*/
        if($OP == $ruta){
            if (is_callable($callback)) {
                return $callback();
            }
        }
    }
      /**
    * 
    * @var $router string de el enrutador para manejar
    * @var funcion callback que se dispara al cumplir la condicion
    *
    **/
    public function post($ruta,$callback=null){
        if($_SERVER['REQUEST_METHOD']!=='POST'){
            return false;
        }  
        
        $URI = $_SERVER['REQUEST_URI'];
        $OP  = $_REQUEST['op'];

        if($OP == $ruta){
            if (is_callable($callback)) {
                return $callback();
            }
        }
    }
      /**
    * 
    * @var $router string de el enrutador para manejar
    * @var funcion callback que se dispara al cumplir la condicion
    *
    **/
    public function put($ruta,$callback=null){
        if($_SERVER['REQUEST_METHOD']!=='PUT'){
            return false;
        }  
        
        $URI = $_SERVER['REQUEST_URI'];
        $OP  = $_REQUEST['op'];
        
        if($OP == $ruta){
            if (is_callable($callback)) {
                return $callback();
            }
        }
    }
      /**
    * 
    * @var $router string de el enrutador para manejar
    * @var funcion callback que se dispara al cumplir la condicion
    *
    **/
    public function delet($ruta,$callback=null){
        if($_SERVER['REQUEST_METHOD']!=='DELETE'){
            return false;
        }  
        
        $URI = $_SERVER['REQUEST_URI'];
        $OP  = $_REQUEST['op'];
        
        if($OP == $ruta){
            if (is_callable($callback)) {
                return $callback();
            }
        }
    }
}

?>