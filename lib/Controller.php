<?php

namespace IceCat;

/**
 * Base controller
 * Author      : Chouaieb Bedoui
 * Date        : 28/04/2022
 * Email       : webm964@gmail.com
 * PHP version : 7.4.28
 */

abstract class Controller{

    
    protected $route_params = [];

    
    public function __construct($route_params){
        $this->route_params = $route_params;
    }

       /**
         * method called when a non-existent or inaccessible method is
         * called on an object of this class. Used to execute before and after
         * filter methods on action methods. Action methods need to be named
         * with an "Action" suffix, e.g. indexAction, showAction etc.
         *
         * @param string $name  Method name
         * @param array $args Arguments passed to the method
         *
         * @return void
         */
    public function __call($name, $args){
        $method = $name . 'Action';
        if (method_exists($this, $method)) {
            if ($this->beforeFilter() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->afterFilter();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

     /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function beforeFilter(){

    }

     /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function afterFilter(){

    }
}