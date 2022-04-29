<?php

namespace App\Controllers;

use \IceCat\View;

/**
 * Homepage controller
 * Author      : Chouaieb Bedoui
 * Date        : 28/04/2022
 * Email       : webm964@gmail.com
 * PHP version : 7.4.28
 */

class Home extends \IceCat\Controller{

   
    public function indexAction(){
        View::renderTemplate('Home/index.html');
    }

    
}