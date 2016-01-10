<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Helpers;
use App\Sql;

class Groups
{
	public function show (AdminController $controller)
	{
		$action = $controller->request->get('mode', null, '');
		$error = '';

		switch ($action)
		{
			case 'add':

				break;

			case 'edit':

				$controller->view->pick('admin/groups_edit');

				$info = $controller->db->query("SELECT * FROM game_users_groups WHERE id = ".$controller->request->get('id', 'int', 0)."")->fetch();

				if (isset($info['id']))
				{
					if ($controller->request->getPost('save', null, '') != '')
					{
						if (!$controller->request->getPost('name', null, ''))
							$error = 'Не указано имя пользователя';
						else
						{
							Sql::build()->update('game_users_groups')->set(Array
							(
								'name' 	=> Helpers::CheckString($controller->request->getPost('name', null, ''))
							))
							->where('id', '=', $info['id'])->execute();

							if (is_array($controller->request->getPost('module', null, '')))
							{
								$m = $controller->request->getPost('module', null, '');

								foreach ($m as $moduleId => $rightId)
								{
									$check = $controller->db->query("SELECT id FROM game_cms_modules WHERE active = '1' AND id = ".intval($moduleId)."")->fetch();

									if (isset($check['id']))
									{
										$rightId = min(2, max(0, $rightId));

										$f = $controller->db->query("SELECT id FROM game_cms_rights WHERE group_id = '".$info['id']."' AND module_id = ".$check['id']."")->fetch();

										if (!isset($f['id']))
										{
											Sql::build()->insert('game_cms_rights')->set(Array
											(
												'group_id' 	=> $info['id'],
												'module_id' => $check['id'],
												'right_id' 	=> $rightId
											))
											->execute();
										}
										else
										{
											Sql::build()->insert('game_cms_rights')->set(Array
											(
												'group_id' 	=> $info['id'],
												'module_id' => $check['id'],
												'right_id' 	=> $rightId
											))
											->where('id', '=', $f['id'])->execute();
										}
									}
								}
							}

							$controller->response->redirect('admin/groups/action/edit/id/'.$info['id'].'/');
						}
					}

					$modules = $controller->db->extractResult($controller->db->query("SELECT * FROM game_cms_modules WHERE active = '1' ORDER BY id ASC"));

					$rights = $controller->db->extractResult($controller->db->query("SELECT * FROM game_cms_rights WHERE group_id = '".$info['id']."'"), 'module_id');

					$controller->view->setVar('rights', $rights);
					$controller->view->setVar('modules', $modules);
					$controller->view->setVar('info', $info);
				}
				else
					$error = 'Группа не найдена';

				break;

			default:

				$controller->view->pick('admin/groups_list');

				$list = $controller->db->extractResult($controller->db->query("SELECT * FROM game_users_groups WHERE 1 ORDER BY id ASC"));

				$controller->view->setVar('list', $list);
		}

		$controller->view->setVar('error', $error);
		$controller->tag->setTitle('Группы пользователей');
	}
}

?>