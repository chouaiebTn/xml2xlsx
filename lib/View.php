<?php

namespace IceCat;

/**
 * View Class
 * Author      : Chouaieb Bedoui
 * Date        : 28/04/2022
 * Email       : webm964@gmail.com
 * PHP version : 7.4.28
 */
class View{

   
    public static function renderView($view, $args = []){
        extract($args, EXTR_SKIP);
        $file = dirname(__DIR__) . "/app/Views/$view"; 

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("View $file not found");
        }
    }

    
    public static function renderTemplate($template, $args = [])
    {
        static $twig = null;
        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/app/Views');
            $twig = new \Twig\Environment($loader);
            $twig->addGlobal('xml_upload_input_name', XML_FILE_UPLOAD_NAME);
        }

        echo $twig->render($template, $args);
    }
}