<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces(array
(
    'App\Models' 		=> APP_PATH.$config->application->modelsDir,
    'App\Controllers' 	=> APP_PATH.$config->application->controllersDir,
    'App' 				=> APP_PATH.$config->application->libraryDir
));

$loader->register();
 
?>