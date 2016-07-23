<?php

namespace Admin\Controllers;

use Admin\Controller;
use App\Helpers;

class ModulesController extends Controller
{
	public function indexAction ()
	{
		$list = $this->db->extractResult($this->db->query("SELECT * FROM game_cms_modules WHERE 1"));

		$this->view->setVar('list', $list);
		$this->tag->setTitle('Настройка модулей');
	}

	public function addAction ()
	{
		$error = '';

		if ($this->request->getPost('save', 'string', '') != '')
		{
			if (!$this->request->getPost('alias', 'string', ''))
				$error = 'Не указан алиас модуля';
			elseif (!$this->request->getPost('name', 'string', ''))
				$error = 'Не указано название модуля';
			else
			{
				$active = $this->request->getPost('active', 'string', '') != '' ? 1 : 0;

				$this->db->insertAsDict('game_cms_modules',
				[
					'active' 	=> $active,
					'alias' 	=> Helpers::CheckString($this->request->getPost('alias', 'string', '')),
					'name' 		=> Helpers::CheckString($this->request->getPost('name', 'string', ''))
				]);

				return $this->response->redirect('admin/modules/edit/'.$this->db->lastInsertId().'/');
			}
		}

		$this->view->setVar('error', $error);

		return true;
	}

	public function editAction ($id)
	{
		$error = '';

		$info = $this->db->query("SELECT * FROM game_cms_modules WHERE id = ".intval($id)."")->fetch();

		if (isset($info['id']))
		{
			if ($this->request->getPost('save', 'string', '') != '')
			{
				if (!$this->request->getPost('alias', 'string', ''))
					$error = 'Не указан алиас модуля';
				elseif (!$this->request->getPost('name', 'string', ''))
					$error = 'Не указано название модуля';
				else
				{
					$active = $this->request->getPost('active', 'string', '') != '' ? 1 : 0;

					$this->db->updateAsDict('game_cms_modules',
					[
						'active' 	=> $active,
						'alias' 	=> Helpers::CheckString($this->request->getPost('alias', 'string', '')),
						'name' 		=> Helpers::CheckString($this->request->getPost('name', 'string', ''))
					], "id = ".$info['id']);

					return $this->response->redirect('admin/modules/edit/'.$info['id'].'/');
				}
			}

			$this->view->setVar('info', $info);
		}

		$this->view->setVar('error', $error);

		return true;
	}
}