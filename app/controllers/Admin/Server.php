<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Server
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 3)
		{
			$controller->view->pick('admin/server');
			$controller->tag->setTitle('Серверное окружение');
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>