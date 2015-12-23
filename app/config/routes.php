<?php

use Phalcon\Mvc\Router;

$router = new Router(true);
$router->removeExtraSlashes(true);

/*
$router->notFound([ "controller" => "error" , "action" => "notFound" ]);
$router->removeExtraSlashes(true);

$router->add('/game', array(
	'controller' => 'game',
));

$router->add('/pers/:action/:params', array
(
	'controller' => 'pers',
	'action' => 1,
	'params' => 2
));

$router->add('/:controller', array
(
	'controller' => 1
));
*/

$router->add('/admin/:action/action/([a-zA-Z0-9_-]+)/:params', array
(
	'controller' 	=> 'admin',
	'action' 		=> 1,
	'mode' 			=> 2,
	'params' 		=> 3
));

$router->add('/', array
(
	'controller' 	=> 'index',
	'action' 		=> 'index',
));

$router->handle();

return $router;
 
?>