<?php

namespace Admin\Controllers;

use Admin\Controller;
use Admin\Forms\GroupForm;
use Friday\Core\Lang;
use Friday\Core\Models\Access;
use Friday\Core\Models\Group;
use Friday\Core\Models\GroupAccess;
use Friday\Core\Helpers\Cache as CacheHelper;

/**
 * @RoutePrefix("/admin/groups")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class GroupsController extends Controller
{
	const CODE = 'groups';

	public function initialize()
	{
		parent::initialize();

		if (!$this->access->canReadController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$this->addToBreadcrumbs(Lang::getText('admin', 'page_title_index'), self::CODE);
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> self::CODE,
			'title' => 'Группы',
			'icon'	=> 'info',
			'sort'	=> 1010
		]];
	}

	public function indexAction ()
	{
		$groups = Group::find();

		$this->view->setVar('groups', $groups);

		$this->tag->setTitle(Lang::getText('admin', 'page_title_index'));
	}

	public function addAction ()
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

		$this->view->setVar('form', $form);

		return true;
	}

	public function editAction ($groupId)
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

		foreach ($access as $module => $data)
		{
			Lang::includeLang('main', $module);
		}

		$this->view->setVar('access', $access);
		$this->view->setVar('access_group', $accessGroup);

		return true;
	}

	public function deleteAction ($groupId)
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

		return $this->response->redirect(self::CODE.'/');
	}
}