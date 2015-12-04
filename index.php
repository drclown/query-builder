<?php
require_once('vendor/autoload.php');

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('views');

$twig = new Twig_Environment($loader, array(
    'cache' => false
));

include("controller.php");