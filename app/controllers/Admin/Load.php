<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Load
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 3)
		{
			$result = array();
			$result['rows'] = array();

			$query = $controller->db->query("SELECT * FROM game_log_load WHERE time >= ".(time() - 86400)." ORDER BY time ASC");

			while ($e = $query->fetch())
			{
				$result['rows'][] = Array
				(
					'TIME'	=> $e['time'],
					'LOAD'	=> json_decode($e['value'], true)
				);
			}

			$controller->view->pick('admin/load');
			$controller->view->setVar('parse', $result);
			$controller->tag->setTitle("Загрузка сервера");
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>