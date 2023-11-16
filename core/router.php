<?php
//CROSS-REEFRENCE PERMITIDOS
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');
/**
* CUSTON ROUTER
**/
class Router {
    
    function __construct(){}
    
    private $routes = [];
    private $middlewares = [];
    /**
    * Métodos para definir una ruta: 
    * GET,POST,PUT,DELETE
    * @param $ruta string de la ruta que se manejará
    * @param $callback función que se ejecutará cuando se cumpla la condición
    **/
    public function middlewares($name, $callback) {
      $this->middlewares[$name] = $callback;
      return $this;
    }

    public function get($route, $callback) {
        $this->routes['GET'][$route] = $callback;
    }
 
    public function post($route, $callback) {
        $this->routes['POST'][$route] = $callback;
    }

    public function put($route, $callback) {
        $this->routes['PUT'][$route] = $callback;
    }

    public function delete($route, $callback) {
        $this->routes['DELETE'][$route] = $callback;
    }
    // Método para manejar la solicitud y ejecutar la función de devolución de llamada correspondiente
    public function run() {
      // Obtenemos el método HTTP y la ruta de la solicitud actual
      $requestMethod = $_SERVER['REQUEST_METHOD'];
        
      $option = $_REQUEST['op'];
        // Verificar si es una solicitud OPTIONS
      if ($requestMethod === 'OPTIONS') {
          
        header('Access-Control-Allow-Origin: *');
          
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
          
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
          
        exit;
      }
      // Inicializamos una variable para indicar si se encontró una ruta correspondiente
      $routeFound = false;
      // Buscamos la ruta correspondiente para el método HTTP actual
      if (isset($this->routes[$requestMethod])) {
        foreach ($this->routes[$requestMethod] as $route => $callback) {
          // Comprobamos si la ruta op
          if ($option === $route) {
            $routeFound = true;
                    
            is_callable($callback) 
            ? call_user_func($callback, $_REQUEST) 
            : false;

            break;
          }
        }
      }
      // Si no se encontró ninguna ruta correspondiente, enviamos una respuesta 404
       if (!$routeFound) {
          header('HTTP/1.1 404 Not Found');
          exit('404 Not Found');
      }
    }
    
    public static function notificacion($rsp,$msn,$id,$data=array()){
      return json_encode(array(
        'rsp'    => $rsp,
        'msg'  => $msn,
        'id'      =>$id,
        'data'  =>$data));
    }
      
    public static function response($data,$recordsResults,  $recordsFiltered,$current_page){
      return json_encode(array(
        'data'              => $data,
        'recordsTotals'     => $recordsResults,
        'recordsFiltered'   => $recordsFiltered,
        'currentPage'      => $current_page));
    }
}
?>

