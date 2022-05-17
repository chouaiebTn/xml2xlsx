<?php 



namespace IceCat;

/**
 * URL Router Class
 * Author      : Chouaieb Bedoui
 * Date        : 28/04/2022
 * Email       : webm964@gmail.com
 * PHP version : 7.4.28
 */

Class Router {


	protected $routes = [];
	protected $params = [];



	public function createRoute($route,$params = []){
		//escape forward slashes in route
		$route = preg_replace('/\//', '\\/', $route);
		// Convert variables 
		$route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);
		$route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

		//add delimiters 
		$route = '/^' . $route . '$/i';

		$this->routes[$route] = $params;
	}


	public function getRoutes(){
		return $this->routes;
	}

	public function matchRoute($uri){
		foreach ($this->routes as $route => $params) {
            if (preg_match($route, $uri, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;
                return true;
            }
        }
        return false;
	}

	public function getParams(){
        return $this->params;
    }


    public function dispatch($uri){
    	$uri = $this->removeVariables($uri);

    	if ($this->matchRoute($uri)) {
            $controller = $this->params['controller'];
            $controller = $this->normalizeControllerName($controller);
            $controller = $this->getNamespace() . $controller;
            if (class_exists($controller)) {
                $controller_object = new $controller($this->params);
                $action = $this->params['action'];
                $action = $this->normalizeActionName($action);

                if (preg_match('/action$/i', $action) == 0) {
                    $controller_object->$action();
                } else {
                    throw new \Exception("Method $action in controller $controller cannot be called directly !");
                }
            } else {
                throw new \Exception("Controller class $controller not found !");
            }
        } else {
            throw new \Exception('Route Was Not Found.', 404);
        }
    }


    protected function normalizeControllerName($string = ''){
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    protected function normalizeActionName($string = ''){
    	 return lcfirst($this->normalizeControllerName($string));
    }


    protected function removeVariables($url){
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    protected function getNamespace(){
        $namespace = 'App\Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }

}