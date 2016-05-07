<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Helpers;

class Modules
{
	public function show (AdminController $controller)
	{
		$action = $controller->request->get('mode', 'string', '');
		$error = '';

		switch ($action)
		{
			case 'add':

				$controller->view->pick('admin/modules_add');

				if ($controller->request->getPost('save', 'string', '') != '')
				{
					if (!$controller->request->getPost('alias', 'string', ''))
						$error = 'Не указан алиас модуля';
					elseif (!$controller->request->getPost('name', 'string', ''))
						$error = 'Не указано название модуля';
					else
					{
						$active = $controller->request->getPost('active', 'string', '') != '' ? 1 : 0;

						$controller->db->insertAsDict('game_cms_modules',
						[
							'active' 	=> $active,
							'alias' 	=> Helpers::CheckString($controller->request->getPost('alias', 'string', '')),
							'name' 		=> Helpers::CheckString($controller->request->getPost('name', 'string', ''))
						]);

						return $controller->response->redirect('admin/modules/action/edit/id/'.$controller->db->lastInsertId().'/');
					}
				}

				break;

			case 'edit':

				$controller->view->pick('admin/modules_edit');

				$info = $controller->db->query("SELECT * FROM game_cms_modules WHERE id = ".$controller->request->get('id', 'int', 0)."")->fetch();

				if (isset($info['id']))
				{
					if ($controller->request->getPost('save', 'string', '') != '')
					{
						if (!$controller->request->getPost('alias', 'string', ''))
							$error = 'Не указан алиас модуля';
						elseif (!$controller->request->getPost('name', 'string', ''))
							$error = 'Не указано название модуля';
						else
						{
							$active = $controller->request->getPost('active', 'string', '') != '' ? 1 : 0;

							$controller->db->updateAsDict('game_cms_modules',
							[
								'active' 	=> $active,
								'alias' 	=> Helpers::CheckString($controller->request->getPost('alias', 'string', '')),
								'name' 		=> Helpers::CheckString($controller->request->getPost('name', 'string', ''))
							], "id = ".$info['id']);

							return $controller->response->redirect('admin/modules/action/edit/id/'.$info['id'].'/');
						}
					}

					$controller->view->setVar('info', $info);
				}

				break;

			default:

				$controller->view->pick('admin/modules_list');

				$list = $controller->db->extractResult($controller->db->query("SELECT * FROM game_cms_modules WHERE 1"));

				$controller->view->setVar('list', $list);
		}

		$controller->view->setVar('error', $error);
		$controller->tag->setTitle('Настройка модулей');
		
		return true;
	}
}

?>