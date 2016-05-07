<?php
namespace Xnova\Admin\Controllers;

use App\Helpers;

class GroupsController extends Application
{
	public function indexAction ()
	{
		$list = $this->db->extractResult($this->db->query("SELECT * FROM game_users_groups WHERE 1 ORDER BY id ASC"));

		$this->view->setVar('list', $list);
		$this->tag->setTitle('Группы пользователей');
	}

	public function addAction ()
	{

	}

	public function editAction ($id)
	{
		$error = '';

		$info = $this->db->query("SELECT * FROM game_users_groups WHERE id = ".intval($id)."")->fetch();

		if (isset($info['id']))
		{
			if ($this->request->getPost('save', null, '') != '')
			{
				if (!$this->request->getPost('name', null, ''))
					$error = 'Не указано имя пользователя';
				else
				{
					$this->db->updateAsDict('game_users_groups', ['name' 	=> Helpers::CheckString($this->request->getPost('name', null, ''))], "id = ".$info['id']);

					if (is_array($this->request->getPost('module', null, '')))
					{
						$m = $this->request->getPost('module', null, '');

						foreach ($m as $moduleId => $rightId)
						{
							$check = $this->db->query("SELECT id FROM game_cms_modules WHERE active = '1' AND id = ".intval($moduleId)."")->fetch();

							if (isset($check['id']))
							{
								$rightId = min(2, max(0, $rightId));

								$f = $this->db->query("SELECT id FROM game_cms_rights WHERE group_id = '".$info['id']."' AND module_id = ".$check['id']."")->fetch();

								if (!isset($f['id']))
								{
									$this->db->insertAsDict('game_cms_rights',
									[
										'group_id' 	=> $info['id'],
										'module_id' => $check['id'],
										'right_id' 	=> $rightId
									]);
								}
								else
								{
									$this->db->insertAsDict('game_cms_rights',
									[
										'group_id' 	=> $info['id'],
										'module_id' => $check['id'],
										'right_id' 	=> $rightId
									]);
								}
							}
						}
					}

					return $this->response->redirect('admin/groups/edit/'.$info['id'].'/');
				}
			}

			$modules = $this->db->extractResult($this->db->query("SELECT * FROM game_cms_modules WHERE active = '1' ORDER BY id ASC"));

			$rights = $this->db->extractResult($this->db->query("SELECT * FROM game_cms_rights WHERE group_id = '".$info['id']."'"), 'module_id');

			$this->view->setVar('rights', $rights);
			$this->view->setVar('modules', $modules);
			$this->view->setVar('info', $info);
		}
		else
			$error = 'Группа не найдена';

		$this->view->setVar('error', $error);

		return true;
	}
}

?>