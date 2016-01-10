<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Errors
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 3)
		{
			if (isset($_GET['delete']))
			{
				$controller->db->query("DELETE FROM game_errors WHERE `error_id` = '" . intval($_GET['delete']) . "'");
			}
			elseif (isset($_GET['deleteall']))
			{
				$controller->db->query("TRUNCATE TABLE game_errors");
			}

			$result = array();
			$result['rows'] = array();

			$query = $controller->db->query("SELECT * FROM game_errors");

			$result['total'] = $query->numRows();

			while ($e = $query->fetch())
			{
				$result['rows'][] = Array
				(
					'ID' 		=> $e['error_id'],
					'TYPE'		=> $e['error_type'],
					'SENDER'	=> $e['error_sender'],
					'TIME'		=> $e['error_time'],
					'TEXT'		=> htmlspecialchars($e['error_text'])
				);
			}

			$controller->view->pick('admin/errors');
			$controller->view->setVar('parse', $result);
			$controller->tag->setTitle("Ошибки SQL");
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>