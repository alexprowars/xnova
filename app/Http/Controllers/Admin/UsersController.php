<?php

namespace Xnova\Http\Controllers\Admin;

use Admin\Forms\UserForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Exceptions\Exception;
use Xnova\Models\Users;
use Xnova\Models\UsersInfo;

class UsersController extends AdminController
{
	public function initialize()
	{
		$this->addToBreadcrumbs(Lang::getText('admin', 'page_title_index'), self::CODE);
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'users',
			'title' => 'Пользователи',
			'icon'	=> 'user',
			'sort'	=> 1000,
			'childrens' => [[
				'code'	=> 'index',
				'title'	=> 'Список',
			], [
				'code'	=> 'ban',
				'title'	=> 'Заблокировать',
			], [
				'code'	=> 'unban',
				'title'	=> 'Разблокировать',
			]],
		], [
			'code' => 'role',
			'url' => backpack_url('role'),
			'title' => 'Роли',
			'icon' => 'group',
			'sort'	=> 1001,
		], [
			'code' => 'permission',
			'url' => backpack_url('permission'),
			'title' => 'Права',
			'icon' => 'key',
			'sort'	=> 1002,
		], [
			'code' => 'setting',
			'url' => backpack_url('setting'),
			'title' => 'Настройки',
			'icon' => 'cog',
			'sort'	=> 1003,
		]];
	}

	public function index ()
	{
		View::share('title', __('admin.page_title_index'));

		return view('admin.users.index', []);
	}

	public function add ()
	{
		if (!$this->user->can('edit users'))
			throw new Exception('Access denied');

		if (request()->isMethod('POST'))
		{
			return redirect()->route('admin.users.edit', ['id' => $this->user->id], false)
				->with('flash|success', 'Пользователь добавлен');
		}

		return view('admin.users.add', ['title_hide' => true]);
	}

	public function edit ($userId)
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

		//$this->view->setVar('form', $form);
		//$this->view->setVar('groups', Group::find());
		//$this->view->setVar('groups_values', $groups);

		return view('admin.users.edit', ['title_hide' => true]);
	}

	public function delete ($userId)
	{
		if (!$this->user->can('edit users'))
			throw new Exception('Access denied');

		$user = Users::query()->find($userId);

		if (!$user)
			return redirect()->route('admin.users')->with('message|error', 'Пользователь не найден');

		if ($user->delete())
			return redirect()->route('admin.users')->with('message|success', 'Пользователь удалён');

		return redirect()->route('admin.users')->with('message|error', 'Произошла ошибка');
	}

	public function list ()
	{
		$limit = Request::post('length', 10);
		$start = Request::post('start', 0);

		$limit = max(10, min(999, $limit));
		$start = max(0, $start);

		$page = floor($start / $limit) + 1;

		$order = 'id asc';

		if (Request::has('order'))
		{
			$columns = Request::post('columns');
			$orders = Request::post('order');

			foreach ($orders as $item)
			{
				if ($columns[$item['column']]['data'] != 'actions')
					$order = $columns[$item['column']]['data'].' '.$item['dir'];
			}
		}

		$search = '';

		if (Request::has('search'))
			$search = Request::post('search')['value'];

		$result = ['data' => []];

		$builder = Users::query()->select(['users.*', 'info.email', 'info.create_time'])
			->join((new UsersInfo())->getTable().' as info', 'info.id', '=', 'users.id')
			->orderByRaw($order);

		if ($search != '')
			$builder->whereRaw('CONCAT(users.username, \' \', info.email) LIKE \'%'.$search.'%\'');

		$paginator = $builder->paginate($limit, null, null, $page);

		$canWrite = $this->user->can('edit users');

		/** @var Users $item */
		foreach ($paginator->items() as $item)
		{
			$result['data'][] = [
				'id' 		=> (int) $item->id,
				'email' 	=> $item->email,
				'name' 		=> $item->getFullName(),
				'date' 		=> date("d.m.Y", $item->create_time),
				'actions'	=> $canWrite,
			];
		}

		$result['draw'] = (int) Request::post('draw', 0);
		$result['recordsTotal'] = $paginator->total();
		$result['recordsFiltered'] = $paginator->total();

		return $result;
	}

	public function find ()
	{
		$result = [];

		$query = Request::query('q', '');

		if (mb_strlen($query) >= 3)
		{
			$users = Users::query()->select(['id', 'CONCAT(name, \' \', last_name) as name'])
				->where('name', 'LIKE', '%'.$query.'%')
				->limit(10)->get();

			foreach ($users as $user)
				$result[] = ['id' => (int) $user->id, 'value' => $user->name];
		}

		return $result;
	}

	public function ban ()
	{
		if ($this->request->getPost('name', 'string', '') != '')
		{
			$name = htmlspecialchars($this->request->getPost('name', 'string', ''));
			$reas = htmlspecialchars($this->request->getPost('why', 'string', ''));

			$days = $this->request->getPost('days', 'int', 0);
			$hour = $this->request->getPost('hour', 'int', 0);
			$mins = $this->request->getPost('mins', 'int', 0);

			$userz = $this->db->query("SELECT id FROM users WHERE username = '" . $name . "';")->fetch();

			if (!isset($userz['id']))
				$this->message(_getText('sys_noalloaw'), 'Игрок не найден');

			$BanTime = $days * 86400;
			$BanTime += $hour * 3600;
			$BanTime += $mins * 60;
			$BanTime += time();

			$this->db->insertAsDict('banned', [
				'who'		=> $userz['id'],
				'theme'		=> $reas,
				'time'		=> time(),
				'longer'	=> $BanTime,
				'author'	=> $this->user->getId(),
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
					$this->registry->resource[212].'_porcent' 	=> 0,
				];

				foreach ($this->registry->reslist['res'] AS $res)
					$arFields[$res.'_mine_porcent'] = 0;

				$this->db->updateAsDict($arFields, "id_owner = ".$userz['id']);
			}

			$this->message(_getText('adm_bn_thpl') . " " . $name . " " . _getText('adm_bn_isbn'), _getText('adm_bn_ttle'));
		}

		View::share('title', __('adm_bn_ttle'));

		return view('admin.users.ban', []);
	}

	public function unban ()
	{
		if ($this->request->getPost('username', 'string', '') != '')
		{
			$info = $this->db->query("SELECT id, username, banned, vacation FROM users WHERE username = '".addslashes($this->request->getPost('username', 'string', ''))."';")->fetch();

			if (isset($info['id']))
			{
				$this->db->query("DELETE FROM banned WHERE who = '" . $info['id'] . "'");
				$this->db->query("UPDATE users SET banned = 0 WHERE id = '" . $info['id'] . "'");

				if ($info['vacation'] == 1)
					$this->db->query("UPDATE users SET vacation = 0 WHERE id = '" . $info['id'] . "'");

				$this->message("Игрок ".$info['username']." разбанен!", 'Информация');
			}
			else
				$this->message("Игрок не найден!", 'Информация');
		}

		View::share('title', 'Разблокировка');

		return view('admin.users.unban', []);
	}
}