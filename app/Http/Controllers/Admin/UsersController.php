<?php

namespace Xnova\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Prologue\Alerts\Facades\Alert;
use Xnova\Models\Banned;
use Xnova\Models;
use Xnova\Models\Users;
use Xnova\Models\UsersInfo;
use Backpack\CRUD\app\Http\Controllers\Operations;
use Xnova\User;
use Xnova\Vars;

/**
 * @property CrudPanel $crud
 */
/** @noinspection PhpUnused */
class UsersController extends CrudController
{
	use ValidatesRequests;
	use Operations\ListOperation;
	use Operations\ShowOperation;
	use Operations\UpdateOperation;
	use Operations\CreateOperation;
	use Operations\DeleteOperation;

	public static function getMenu()
	{
		return [[
			'code'	=> 'users',
			'title' => 'Пользователи',
			'url'	=> backpack_url('users'),
			'icon'	=> 'user',
			'sort'	=> 1000,
			'childrens' => [[
				'code'	=> 'index',
				'title'	=> 'Список',
			], [
				'code'	=> 'ban',
				'url'	=> backpack_url('users/ban'),
				'title'	=> 'Заблокировать',
			], [
				'code'	=> 'unban',
				'url'	=> backpack_url('users/unban'),
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

	public function setup()
	{
		$this->crud->setModel(Users::class);
		$this->crud->setEntityNameStrings('пользователь', 'пользователи');
		$this->crud->setRoute(backpack_url('users'));

		$this->crud->operation('list', function () {
			$this->crud->orderBy('id', 'desc');
			$this->crud->enableExportButtons();

			$this->crud->setColumns([
				[
					'name'  => 'id',
					'label' => 'ID',
				], [
					'name'  => 'username',
					'label' => 'Никнейм',
				], [
					'name'  => 'galaxy',
					'label' => 'Галактика',
				], [
					'name'  => 'system',
					'label' => 'Система',
				], [
					'name'  => 'planet',
					'label' => 'Планета',
				],  [
					'label' => 'Email',
					'type' => 'select',
					'name' => 'email',
					'entity' => 'info',
					'attribute' => 'email',
					'model' => UsersInfo::class
				], [
					'label' => 'Дата регистрации',
					'type' => 'closure',
					'name' => 'create_time',
					'entity' => 'info',
					'attribute' => 'create_time',
					'model' => UsersInfo::class,
					'function' => function ($entry) {
						return date('d.m.Y H:i:s', $entry->info->create_time ?? 0);
					},
				], [
					'label'     => trans('backpack::permissionmanager.roles'),
					'type'      => 'select_multiple',
					'name'      => 'roles',
					'entity'    => 'roles',
					'attribute' => 'name',
					'model'     => config('permission.models.role'),
				],
			]);
		});

		$this->crud->addField([
			'name'	=> 'email',
			'label'	=> 'Email',
			'type'	=> 'email',
			'entity' => 'info',
			'attribute' => 'email',
			'model' => UsersInfo::class
		]);

		$this->crud->operation('update', function () {
			$this->crud->addField([
				'name'	=> 'username',
				'label'	=> 'Никнейм',
				'type'	=> 'text',
			]);

			$this->crud->addField([
				'label'             => trans('backpack::permissionmanager.user_role_permission'),
				'field_unique_name' => 'user_role_permission',
				'type'              => 'checklist_dependency',
				'name'              => [
					'roles',
					'permissions',
				],
				'subfields' => [
					'primary' => [
						'label'            => trans('backpack::permissionmanager.roles'),
						'name'             => 'roles',
						'entity'           => 'roles',
						'entity_secondary' => 'permissions',
						'attribute'        => 'name',
						'model'            => config('permission.models.role'),
						'pivot'            => true,
						'number_columns'   => 3,
					],
					'secondary' => [
						'label'          => ucfirst(trans('backpack::permissionmanager.permission_singular')),
						'name'           => 'permissions',
						'entity'         => 'permissions',
						'entity_primary' => 'roles',
						'attribute'      => 'name',
						'model'          => config('permission.models.permission'),
						'pivot'          => true,
						'number_columns' => 3,
					],
				],
			]);
		});

		$this->crud->operation('create', function () {
			$this->crud->addField([
				'name'  => 'password',
				'label' => trans('backpack::permissionmanager.password'),
				'type'  => 'password',
			]);

			$this->crud->addField([
				'name'  => 'password_confirmation',
				'label' => trans('backpack::permissionmanager.password_confirmation'),
				'type'  => 'password',
			]);
		});
	}

	public function store()
	{
		$this->crud->applyConfigurationFromSettings('create');
		$this->crud->hasAccessOrFail('create');

		$this->crud->validateRequest();

		$fields = $this->crud->getStrippedSaveRequest();

		$userId = User::creation([
			'email' => $fields['email'],
			'password' => $fields['password'],
		]);

		Alert::success(trans('backpack::crud.insert_success'))->flash();

		$this->crud->setSaveAction();

		return $this->crud->performSaveAction($userId);
	}

	public function ban(Request $request)
	{
		if ($request->isMethod('POST')) {
			$name = htmlspecialchars($request->post('name', ''));
			$reason = htmlspecialchars($request->post('why', ''));

			$days = $request->post('days', 0);
			$hour = $request->post('hour', 0);
			$mins = $request->post('mins', 0);

			$user = Users::query()->where('username', $name)->first();

			if (!$user) {
				return redirect(backpack_url('users/ban'))->with('error', 'Игрок не найден');
			}

			$BanTime = $days * 86400;
			$BanTime += $hour * 3600;
			$BanTime += $mins * 60;
			$BanTime += time();

			Banned::query()->insert([
				'who'		=> $user->id,
				'theme'		=> $reason,
				'time'		=> time(),
				'longer'	=> $BanTime,
				'author'	=> Auth::id(),
			]);

			$update = ['banned' => $BanTime];

			if ($request->post('ro', 0) == 1) {
				$update['vacation'] = 1;
			}

			$user->update($update);

			if ($request->post('ro', 0) == 1) {
				$buildsId = [4, 12, 212];

				foreach (Vars::getResources() as $res) {
					$buildsId[] = Vars::getIdByName($res . '_mine');
				}

				Models\PlanetsBuildings::query()->whereIn('planet_id', User::getPlanetsId($user->id))
					->whereIn('build_id', $buildsId)
					->update(['power' => 0]);

				Models\PlanetsUnits::query()->whereIn('planet_id', User::getPlanetsId($user->id))
					->whereIn('unit_id', $buildsId)
					->update(['power' => 0]);
			}

			return redirect(backpack_url('users/ban'))->with('success', 'Игрок "' . $name . '" добавлен в список блокировки');
		}

		View::share('title', 'Блокировка доступа');
		View::share('breadcrumbs', [
			'Панель управления' => backpack_url('/'),
			'Блокировка доступа' => false,
		]);

		return view('admin.users.ban', []);
	}

	public function unban(Request $request)
	{
		if ($request->isMethod('POST') != '') {
			$fields = $this->validate($request, [
				'username' => 'required',
			], [
				'required' => 'Поле ":attribute" обязательно для заполнения',
			]);

			$user = Users::query()->where('username', $fields['username'])->get(['id', 'username', 'banned', 'vacation'])->first();

			if (!$user) {
				return redirect(backpack_url('users/unban'))->with('error', 'Игрок не найден');
			}

			Banned::query()->where('who', $user->id)->delete();
			$user->banned = 0;

			if ($user->vacation == 1) {
				$user->vacation = 0;
			}

			$user->save();

			return redirect(backpack_url('users/unban'))->with('error', 'Игрок "' . $user->username . '" разбанен!');
		}

		View::share('title', 'Разблокировка доступа');
		View::share('breadcrumbs', [
			'Панель управления' => backpack_url('/'),
			'Разблокировка доступа' => false,
		]);

		return view('admin.users.unban', []);
	}
}
