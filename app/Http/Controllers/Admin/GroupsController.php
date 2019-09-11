<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;

class GroupsController extends AdminController
{
	public function initialize()
	{
		$this->addToBreadcrumbs(Lang::getText('admin', 'page_title_index'), self::CODE);
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'groups',
			'title' => 'Группы',
			'icon'	=> 'info',
			'sort'	=> 1010
		]];
	}

	public function index ()
	{
		$groups = Group::find();

		View::share('title', __('admin.page_title_index'));

		return view('admin.groups.index', ['groups' => $groups]);
	}

	public function add ()
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$group = new Group();
		$form = new GroupForm($group);
		$form->setAction($this->url->get(self::CODE.'/add/'));

		if ($this->request->isPost())
		{
			if ($form->isValid($this->request->getPost()))
			{
				if ($group->create())
					return $this->response->redirect(self::CODE.'/');
				else
					$this->flashSession->error('Произошла ошибка при сохранении группы');
			}
			else
			{
				foreach ($form->getMessages() as $message)
				{
					$this->flashSession->error($message);
				}
			}
		}

		return view('admin.groups.add', []);
	}

	public function edit ($groupId)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$group = Group::findFirst($groupId);

		if (!$group)
		{
			$this->flashSession->error('Группа не найден');

			return $this->response->redirect(self::CODE.'/');
		}

		$accessGroup = [];

		$data = GroupAccess::find(["columns" => "access_id", "conditions" => "group_id = :group:", "bind" => ["group" => $group->id]]);

		foreach ($data as $item)
			$accessGroup[] = $item->access_id;

		$form = new GroupForm($group);
		$form->setAction($this->url->get(self::CODE.'/edit/'.$group->id.'/'));

		if ($this->request->isPost())
		{
			if ($form->isValid($this->request->getPost()))
			{
				if ($group->update())
				{
					$access = $form->getValue('roles');

					$accessIds = [];

					foreach ($access as $module => $roles)
					{
						foreach ($roles as $code => $id)
							$accessIds[] = $id;
					}

					if (is_array($accessIds))
					{
						foreach ($accessIds as $id)
						{
							if (!in_array($id, $accessGroup))
							{
								$access = Access::findFirst($id);

								if ($access)
								{
									$item = new GroupAccess();
									$item->create(['group_id' => $group->id, 'access_id' => $access->id]);
								}
							}
						}

						$diff = array_diff($accessGroup, $accessIds);

						if (count($diff))
							GroupAccess::find(["conditions" => "group_id = :group: AND access_id IN (".implode(',', $diff).")", "bind" => ["group" => $group->id]])->delete();
					}
					else
						GroupAccess::find(["conditions" => "group_id = :group:", "bind" => ["group" => $group->id]])->delete();

					CacheHelper::clearApplicationCache();

					$this->flashSession->success('Изменения сохранены');

					return $this->response->redirect(self::CODE.'/edit/'.$group->id.'/');
				}
				else
					$this->flashSession->error('Произошла ошибка при сохранении группы');
			}
			else
			{
				foreach ($form->getMessages() as $message)
				{
					$this->flashSession->error($message);
				}
			}
		}

		$this->view->setVar('form', $form);

		$data = Access::find();

		$access = [];

		foreach ($data as $item)
		{
			if (!isset($access[$item->module]))
				$access[$item->module] = [];

			$access[$item->module][$item->code] = $item->id;
		}

		return view('admin.groups.edit', []);
	}

	public function delete ($groupId)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$group = Group::findFirst($groupId);

		if (!$group)
		{
			$this->flashSession->error('Группа не найдена');

			return $this->response->redirect(self::CODE.'/');
		}

		if ($group->isSystem())
		{
			$this->flashSession->error('Группа не может быть удалена');

			return $this->response->redirect(self::CODE.'/');
		}

		if ($group->delete())
			$this->flashSession->success('Группа удалёна');
		else
			$this->flashSession->error('Произошла ошибка');

		return redirect('admin/groups');
	}
}