<?php 

/**
 * Application Entry Point
 * Author      : Chouaieb Bedoui
 * Date        : 28/04/2022
 * Email       : webm964@gmail.com
 * PHP version : 7.4.28
 */

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/config.php';




//log or show errors

set_error_handler('IceCat\Error::handleErrors');
set_exception_handler('IceCat\Error::handleException');


//new router instance
$app = new IceCat\Router();

//add routes
$app->createRoute('', ['controller' => 'Home', 'action' => 'index']);
$app->createRoute('{controller}/{action}');

//dispatch route
$app->dispatch($_SERVER['QUERY_STRING']);




?>