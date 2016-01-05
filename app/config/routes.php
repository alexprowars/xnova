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

$router->add('/:controller/:action/:params', array
(
	'controller' 	=> 1,
	'action' 		=> 2,
	'params' 		=> 3
));

$router->add('/galaxy/:params', array
(
	'controller' 	=> 'galaxy',
	'action' 		=> 'index',
	'params' 		=> 1,
));

$router->add('/galaxy/:int/:int/:params', array
(
	'controller' 	=> 'galaxy',
	'action' 		=> 'index',
	'galaxy' 		=> 1,
	'system' 		=> 2,
	'r' 			=> 3,
));

$router->add('/galaxy/:int/:int/:int/:params', array
(
	'controller' 	=> 'galaxy',
	'action' 		=> 'index',
	'galaxy' 		=> 1,
	'system' 		=> 2,
	'r' 			=> 3,
	'params' 		=> 4
));

$router->add('/fleet/g([0-9]{1,2})/s([0-9]{1,3})/p([0-9]{1,2})/t([0-9]{1})/m([0-9]{1,2})/:params', array
(
	'controller' 	=> 'fleet',
	'action' 		=> 'index',
	'galaxy' 		=> 1,
	'system' 		=> 2,
	'planet' 		=> 3,
	'type' 			=> 4,
	'mission' 		=> 5,
	'params' 		=> 6
));

$router->add('/info/([0-9])/:params', array
(
	'controller' 	=> 'info',
	'action' 		=> 'index',
	'gid' 			=> 1,
	'params' 		=> 2
));

$router->add('/players/([0-9])/:params', array
(
	'controller' 	=> 'players',
	'action' 		=> 'index',
	'id' 			=> 1,
	'params' 		=> 2
));

$router->add('/messages/write/([0-9])/:params', array
(
	'controller' 	=> 'messages',
	'action' 		=> 'write',
	'id' 			=> 1,
	'params' 		=> 2
));

$router->add('/players/stat/([0-9])/:params', array
(
	'controller' 	=> 'players',
	'action' 		=> 'stat',
	'id' 			=> 1,
	'params' 		=> 2
));

$router->add('/rw/([0-9]+)/([a-z0-9]+)/:params', array
(
	'controller' 	=> 'rw',
	'action' 		=> 'index',
	'id' 			=> 1,
	'k' 			=> 2,
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