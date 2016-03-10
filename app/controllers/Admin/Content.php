<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Content
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 3)
		{
			$result = [];

			if ($controller->request->getQuery('edit') > 0)
			{
				$result['info'] = $controller->db->query("SELECT * FROM game_content WHERE id = '".$controller->request->getQuery('edit', 'int', 0)."'")->fetch();

				$controller->view->pick('admin/content_edit');
			}
			else
			{
				$result['rows'] = [];

				$query = $controller->db->query("SELECT * FROM game_content");

				$result['total'] = $query->numRows();

				while ($e = $query->fetch())
				{
					$result['rows'][] = $e;
				}

				$controller->view->pick('admin/content_row');
			}

			$controller->view->setVar('parse', $result);
			$controller->tag->setTitle("Контент");
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>