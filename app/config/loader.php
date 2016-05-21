<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces([
    'App\Models' 	=> APP_PATH.$config->application->baseDir.$config->application->modelsDir,
    'App' 			=> APP_PATH.$config->application->baseDir.$config->application->libraryDir
]);

$loader->register();