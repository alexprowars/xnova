<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Tree
{
	public function show (AdminController $controller)
	{
		$action = $controller->request->get('mode', 'string', '');

		switch ($action)
		{
			case 'node':

				header('Content-type: application/json; charset=utf-8');

				$result = array();

				$parent = $controller->request->get('parent', 'int', 0);

				$nodes = $controller->getMenu($parent, 2);

				foreach ($nodes AS $node)
				{
					$result[] = array
					(
						'id' 		=> $node['id'],
						'text' 		=> $node['name'],
						'type'		=> (count($node['children']) > 0) ? 'folder' : 'file',
						'children' 	=> (count($node['children']) > 0),
						'state'		=> array('opened' => false)
					);
				}

				echo json_encode($result);
				die();

				break;

			default:

				$controller->view->pick('admin/tree_list');

		}

		$controller->tag->setTitle('Структура');
	}
}

?>