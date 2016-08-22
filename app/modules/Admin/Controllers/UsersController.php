<?php

namespace Admin\Controllers;

use Admin\Controller;
use Friday\Core\Form\Builder;
use Admin\Forms\UserForm;
use Friday\Core\Lang;
use Friday\Core\Mail\PHPMailer;
use Friday\Core\Modules;
use Friday\Core\Models\Group;
use Friday\Core\Models\User;
use Friday\Core\Models\UserGroup;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * @RoutePrefix("/admin/users")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class UsersController extends Controller
{
	const CODE = 'users';

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
			'title' => 'Пользователи',
			'icon'	=> 'user',
			'sort'	=> 1000,
			'url'	=> '',
			'childrens' => [
				[
					'code'	=> 'index',
					'url'	=> self::CODE,
					'title'	=> 'Список'
				],
				[
					'code'	=> 'ban',
					'url'	=> self::CODE.'/ban',
					'title'	=> 'Заблокировать'
				],
				[
					'code'	=> 'unban',
					'url'	=> self::CODE.'/unban',
					'title'	=> 'Разблокировать'
				]
			]
		]];
	}

	public function indexAction ()
	{
		$this->assets->addJs('/assets/admin/global/js/datatable.js');
		$this->assets->addJs('/assets/admin/global/plugins/datatables/datatables.min.js');
		$this->assets->addJs('/assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');

		$this->assets->addCss('/assets/admin/global/plugins/datatables/datatables.min.css', 100);
		$this->assets->addCss('/assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css', 101);

		$this->tag->setTitle(Lang::getText('admin', 'page_title_index'));
	}

	public function addAction ()
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$user = new User;
		$form = new UserForm($user);
		$form->setAction($this->url->get('users/add/'));

		if ($this->request->isPost())
		{
			if ($form->isValid($this->request->getPost()))
			{
				if ($this->request->hasFiles())
				{
					$files = $this->request->getUploadedFiles();

					foreach ($files as $file)
					{
						$user->uploadPhoto($file);

						break;
					}
				}

				if ($user->create())
				{
					$mail = new PHPMailer();
					$mail->setFrom($this->config->app->email, $this->config->app->name);
					$mail->addAddress($user->email);
					$mail->isHTML(true);
					$mail->CharSet = 'utf-8';
					$mail->Subject = $this->config->app->name.": Регистрация";

					$view = $this->view;

					$content = $view->getRender('mail', 'register',
						[
							"password" 	=> $this->request->getPost('password'),
							"name" 		=> $user->name,
							"last_name" => $user->last_name,
							"email" 	=> $user->email,
						],
						function (View $view)
						{
							$view->setRenderLevel(View::LEVEL_LAYOUT);
						}
					);

					$mail->Body = $content;
					$mail->send();

					$this->flashSession->success('Пользователь добавлен');

					return $this->response->redirect('users/edit/'.$user->getId().'/');
				}
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
		$this->view->setVar('title_hide', VALUE_TRUE);
		
		return true;
	}

	public function editAction ($userId)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$user = User::findFirst($userId);

		if (!$user)
		{
			$this->flashSession->error('Пользователь не найден');
			
			return $this->response->redirect(self::CODE.'/');
		}

		if ($this->access->hasAccess('loyalty'))
			Modules::init('loyalty');

		$form = new UserForm($user);
		$form->setAction($this->url->get('users/edit/'.$user->id.'/'));

		$groups = [];

		foreach ($user->groups as $item)
		{
			$groups[] = $item->group_id;
		}

		if ($this->request->isPost())
		{
			if ($form->isValid($this->request->getPost()))
			{
				if ($this->request->hasPost('photo_delete'))
					$user->deletePhoto();

				if ($this->request->hasFiles())
				{
					$files = $this->request->getUploadedFiles();

					foreach ($files as $file)
					{
						$user->uploadPhoto($file);

						break;
					}
				}

				$groupsResult = [];

				$formGroups = $form->getValue('groups_id');

				if (is_array($formGroups) && count($formGroups))
				{
					foreach ($formGroups as $groupId)
					{
						if (!in_array($groupId, $groups))
						{
							$group = Group::findFirst($groupId);

							if ($group)
							{
								$item = new UserGroup();

								$item->group_id = $group->id;
								$item->user_id = $user->id;

								if ($item->create())
									$groupsResult[] = $item->group_id;
							}
						}
						else
							$groupsResult[] = $groupId;
					}

					$diff = array_diff($groups, $formGroups);

					if (count($diff))
						UserGroup::find(["conditions" => "user_id = :user: AND group_id IN (".implode(',', $diff).")", "bind" => ["user" => $user->id]])->delete();
				}
				else
					UserGroup::find(["conditions" => "user_id = :user:", "bind" => ["user" => $user->id]])->delete();

				$groups = $groupsResult;

				if ($user->update())
				{
					$this->flashSession->success('Изменения сохранены');

					return $this->response->redirect(self::CODE.'/edit/'.$user->getId().'/');
				}
			}
			else
			{
				foreach ($form->getMessages() as $message)
				{
					$this->flashSession->error($message);
				}
			}
		}

		$tabGroups = new Builder\Tab('Группы', 'groups');
		$tabGroups->setPartial('users/tab_group');

		$form->addTab($tabGroups);

		$this->view->setVar('form', $form);
		$this->view->setVar('groups', Group::find());
		$this->view->setVar('groups_values', $groups);

		$this->assets->addJs('assets/admin/global/js/datatable.js');
		$this->assets->addJs('assets/admin/global/plugins/datatables/datatables.min.js');
		$this->assets->addJs('assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');

		$this->assets->addCss('assets/admin/global/plugins/datatables/datatables.min.css', 100);
		$this->assets->addCss('assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css', 101);

		$this->view->setVar('title_hide', VALUE_TRUE);

		return true;
	}

	public function deleteAction ($userId)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$user = User::findFirst($userId);

		if (!$user)
		{
			$this->flashSession->error('Пользователь не найден');
			return $this->response->redirect('users/');
		}

		if ($user->delete())
			$this->flashSession->success('Пользователь удалён');
		else
			$this->flashSession->error('Произошла ошибка');

		return $this->response->redirect('users/');
	}

	public function listAction ()
	{
		$limit = $this->request->getPost('length', 'int', 10);
		$start = $this->request->getPost('start', 'int', 0);

		if ($limit < 0)
			$limit = 999;
		if ($limit < 10)
			$limit = 10;
		if ($start < 0)
			$start = 0;

		$page = intval($start / $limit) + 1;

		$order = 'id asc';

		if ($this->request->hasPost('order'))
		{
			$columns = $this->request->getPost('columns');
			$orders = $this->request->getPost('order');

			foreach ($orders as $item)
			{
				if ($columns[$item['column']]['data'] != 'actions')
					$order = $columns[$item['column']]['data'].' '.$item['dir'];
			}
		}

		$search = '';

		if ($this->request->hasPost('search'))
			$search = $this->request->getPost('search')['value'];

		$result = ['data' => []];

		$builder = $this->modelsManager->createBuilder()->columns('user.*, 1')->from(['user' => 'Friday\Core\Models\User'])->orderBy($order);

		if ($search != '')
			$builder->where('CONCAT(user.email, \' \', user.name, \' \', user.last_name, \' \', user.second_name) LIKE \'%'.$search.'%\'');

		$paginator = new PaginatorQueryBuilder([
			"builder"	=> $builder,
			"limit"		=> $limit,
			"page"		=> $page
		]);

		$page = $paginator->getPaginate();

		$canWrite = $this->access->canWriteController(self::CODE, 'admin');

		foreach ($page->items as $item)
		{
			$result['data'][] = [
				'id' 		=> (int) $item->user->id,
				'email' 	=> $item->user->email,
				'name' 		=> $item->user->getFullName(),
				'date' 		=> date("d.m.Y", strtotime($item->user->create_date)),
				'actions'	=> $canWrite ? '
					<a href="'.$this->url->get('users/edit/'.$item->user->id.'/').'" class="btn btn-outline btn-sm purple"><i class="fa fa-edit"></i> Изменить </a>
					<a href="javascript:;" onclick="if (window.confirm(\'Удалить пользователя\')) location.href=\''.$this->url->get('users/delete/'.$item->user->id.'/').'\';" class="btn btn-outline dark btn-sm black"><i class="fa fa-trash-o"></i> Удалить </a>
				' : ''
			];
		}

		$result['draw'] = (int) $this->request->getPost('draw', 'int', 0);
		$result['recordsTotal'] = $page->total_items;
		$result['recordsFiltered'] = $page->total_items;

		return $this->response->setJsonContent($result);
	}

	public function findAction ()
	{
		$result = [];

		$query = $this->request->getQuery('q', 'string', '');

		if (mb_strlen($query) >= 3)
		{
			$users = User::find([
				'columns' => 'id, CONCAT(name, \' \', last_name) as name',
				'limit' => 10,
				'conditions' => "CONCAT(name, ' ', second_name, ' ', last_name, ' ', email) LIKE :query:",
				'bind' => ['query' => '%'.$query.'%']
			]);

			foreach ($users as $user)
			{
				$result[] = ['id' => (int) $user->id, 'value' => $user->name];
			}
		}

		return $this->response->setJsonContent($result);
	}

	public function banAction ()
	{
		if ($this->request->getPost('name', 'string', '') != '')
		{
			$name = htmlspecialchars($this->request->getPost('name', 'string', ''));
			$reas = htmlspecialchars($this->request->getPost('why', 'string', ''));

			$days = $this->request->getPost('days', 'int', 0);
			$hour = $this->request->getPost('hour', 'int', 0);
			$mins = $this->request->getPost('mins', 'int', 0);

			$userz = $this->db->query("SELECT id FROM game_users WHERE username = '" . $name . "';")->fetch();

			if (!isset($userz['id']))
				$this->message(_getText('sys_noalloaw'), 'Игрок не найден');

			$BanTime = $days * 86400;
			$BanTime += $hour * 3600;
			$BanTime += $mins * 60;
			$BanTime += time();

			$this->db->insertAsDict('game_banned', [
				'who'		=> $userz['id'],
				'theme'		=> $reas,
				'time'		=> time(),
				'longer'	=> $BanTime,
				'author'	=> $this->user->getId()
			]);

			$update = ['banned' => $BanTime];

			if ($this->request->getPost('ro', 'int', 0) == 1)
				$update['vacation'] = 1;

			$this->user->saveData($update, $userz['id']);

			if ($this->request->getPost('ro', 'int', 0) == 1)
			{
				$arFields = [
					$this->registry->resource[4].'_porcent' 	=> 0,
					$this->registry->resource[12].'_porcent' 	=> 0,
					$this->registry->resource[212].'_porcent' 	=> 0
				];

				foreach ($this->registry->reslist['res'] AS $res)
					$arFields[$res.'_mine_porcent'] = 0;

				$this->db->updateAsDict($arFields, "id_owner = ".$userz['id']);
			}

			$this->message(_getText('adm_bn_thpl') . " " . $name . " " . _getText('adm_bn_isbn'), _getText('adm_bn_ttle'));
		}

		$this->tag->setTitle(_getText('adm_bn_ttle'));
	}

	public function unbanAction ()
	{
		if ($this->request->getPost('username', 'string', '') != '')
		{
			$info = $this->db->query("SELECT id, username, banned, vacation FROM game_users WHERE username = '".addslashes($this->request->getPost('username', 'string', ''))."';")->fetch();

			if (isset($info['id']))
			{
				$this->db->query("DELETE FROM game_banned WHERE who = '" . $info['id'] . "'");
				$this->db->query("UPDATE game_users SET banned = 0 WHERE id = '" . $info['id'] . "'");

				if ($info['vacation'] == 1)
					$this->db->query("UPDATE game_users SET vacation = 0 WHERE id = '" . $info['id'] . "'");

				$this->message("Игрок ".$info['username']." разбанен!", 'Информация');
			}
			else
				$this->message("Игрок не найден!", 'Информация');
		}

		$this->tag->setTitle('Разблокировка');
	}
}