<?php

use Phalcon\Mvc\Router;

$router = new Router(true);
$router->removeExtraSlashes(true);
$router->setDefaultModule('game');

$router->add('/:controller/:action/:params',
[
	'controller' 	=> 1,
	'action' 		=> 2,
	'params' 		=> 3
]);

$router->add('/galaxy/:params',
[
	'controller' 	=> 'galaxy',
	'action' 		=> 'index',
	'params' 		=> 1,
]);

$router->add('/galaxy/([0-9]{1,2})/([0-9]{1,3})/:params',
[
	'controller' 	=> 'galaxy',
	'action' 		=> 'index',
	'galaxy' 		=> 1,
	'system' 		=> 2,
	'r' 			=> '3',
	'params' 		=> 3
]);

$router->add('/galaxy/([0-9]{1,2})/([0-9]{1,3})/([0-9])/:params',
[
	'controller' 	=> 'galaxy',
	'action' 		=> 'index',
	'galaxy' 		=> 1,
	'system' 		=> 2,
	'r' 			=> 3,
	'params' 		=> 4
]);

$router->add('/fleet/g([0-9]{1,2})/s([0-9]{1,3})/p([0-9]{1,2})/t([0-9]{1})/m([0-9]{1,2})/:params',
[
	'controller' 	=> 'fleet',
	'action' 		=> 'index',
	'galaxy' 		=> 1,
	'system' 		=> 2,
	'planet' 		=> 3,
	'type' 			=> 4,
	'mission' 		=> 5,
	'params' 		=> 6
]);

$router->add('/info/([0-9]+)/:params',
[
	'controller' 	=> 'info',
	'action' 		=> 'index',
	'gid' 			=> 1,
	'params' 		=> 2
]);

$router->add('/players/([0-9]+)/:params',
[
	'controller' 	=> 'players',
	'action' 		=> 'index',
	'id' 			=> 1,
	'params' 		=> 2
]);

$router->add('/messages/write/([0-9]+)/:params',
[
	'controller' 	=> 'messages',
	'action' 		=> 'write',
	'id' 			=> 1,
	'params' 		=> 2
]);

$router->add('/players/stat/([0-9]+)/:params',
[
	'controller' 	=> 'players',
	'action' 		=> 'stat',
	'id' 			=> 1,
	'params' 		=> 2
]);

$router->add('/rw/([0-9]+)/([a-z0-9]+)/:params',
[
	'controller' 	=> 'rw',
	'action' 		=> 'index',
	'id' 			=> 1,
	'k' 			=> 2,
	'params' 		=> 3
]);

$router->add('/tech/([0-9]+)/:params',
[
	'controller' 	=> 'tech',
	'action' 		=> 'info',
	'id' 			=> 1,
	'params' 		=> 2
]);

$router->add('/log/([0-9]+)/:params',
[
	'controller' 	=> 'log',
	'action' 		=> 'info',
	'id' 			=> 1,
	'params' 		=> 2
]);

$router->add('/tutorial/([0-9]+)/:params',
[
	'controller' 	=> 'tutorial',
	'action' 		=> 'info',
	'id' 			=> 1,
	'params' 		=> 2
]);

$router->add('/sim/([0-9!;,]+)/:params',
[
	'controller' 	=> 'sim',
	'action' 		=> 'index',
	'data' 			=> 1,
	'params' 		=> 2
]);

$router->add('/content/([a-zA-Z0-9]+)/:params',
[
	'controller' 	=> 'content',
	'action' 		=> 'index',
	'article' 		=> 1,
	'params' 		=> 2
]);

$router->add('/login/:params',
[
	'controller' 	=> 'index',
	'action' 		=> 'login',
	'params' 		=> 1
]);

$router->add('/',
[
	'controller' 	=> 'index',
	'action' 		=> 'index',
]);

$router->add('/admin/:params',
[
	'module' 		=> 'admin',
	'controller' 	=> 'overview',
	'action' 		=> 'index',
	'params' 		=> 1
])->setName('admin');

$router->add('/admin/:controller/:params',
[
	'module' 		=> 'admin',
	'controller' 	=> 1,
	'action' 		=> 'index',
	'params' 		=> 2
]);

$router->add('/admin/:controller/:action/:params',
[
	'module' 		=> 'admin',
	'controller' 	=> 1,
	'action' 		=> 2,
	'params' 		=> 3
]);

$router->handle();

return $router;