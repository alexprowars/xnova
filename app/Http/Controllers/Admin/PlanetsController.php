<?php

namespace App\Http\Controllers\Admin;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Galaxy;
use App\Http\Requests\Admin\PlanetRequest;
use App\Models\Planet;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;

class PlanetsController extends CrudController
{
	use Operations\ListOperation;
	use Operations\UpdateOperation;
	use Operations\CreateOperation;
	use Operations\ShowOperation;
	use Operations\DeleteOperation;

	public static function getMenu()
	{
		return [[
			'code'	=> 'planets',
			'title' => 'Список планет',
			'url'	=> backpack_url('planets'),
			'icon'	=> 'la la-globe',
			'sort'	=> 80,
		]];
	}

	public function setup()
	{
		$this->crud->setModel(Planet::class);
		$this->crud->setEntityNameStrings('планету', 'планеты');
		$this->crud->setRoute(backpack_url('planets'));
	}

	public function setupListOperation()
	{
		$this->crud->orderBy('id', 'desc');
		$this->crud->setColumns([[
			'name'  => 'id',
			'label' => 'ID',
		], [
			'name'  => 'name',
			'label' => 'Название',
		], [
			'name'  => 'galaxy',
			'label' => 'Галактика',
		], [
			'name'  => 'system',
			'label' => 'Система',
		], [
			'name'  => 'planet',
			'label' => 'Планета',
		], [
			'name'  => 'planet_type',
			'label' => 'Тип',
			'type'	=> 'enum',
			'enum_class' => PlanetType::class,
			'enum_function' => 'title',
		], [
			'name'  => 'last_update',
			'label' => 'Время обновления',
			'type'  => 'datetime',
		]]);
	}

	public function setupShowOperation()
	{
		$this->crud->setColumns([[
			'name'  => 'id',
			'label' => 'ID',
		], [
			'name'  => 'name',
			'label' => 'Название',
		], [
			'name'  => 'galaxy',
			'label' => 'Галактика',
			'type' => 'number',
		], [
			'name'  => 'system',
			'label' => 'Система',
			'type' => 'number',
		], [
			'name'  => 'planet',
			'label' => 'Планета',
			'type' => 'number',
		], [
			'name'  => 'planet_type',
			'label' => 'Тип',
			'type'	=> 'enum',
			'enum_class' => PlanetType::class,
			'enum_function' => 'title',
		], [
			'name'  => 'last_update',
			'label' => 'Время обновления',
			'type'  => 'datetime',
		], [
			'name'  => 'last_active',
			'label' => 'Время активности',
			'type'  => 'datetime',
		], [
			'name'  => 'destruyed',
			'label' => 'Время уничтожения',
			'type'  => 'datetime',
		], [
			'name'  => 'merchand',
			'label' => 'Время покупки ресурсов',
			'type'  => 'datetime',
		], [
			'name'  => 'image',
			'label' => 'Картинка',
		], [
			'name'  => 'diameter',
			'label' => 'Диаметр',
			'type' => 'number',
			'thousands_sep' => ' ',
			'dec_point'     => ',',
		], [
			'name'  => 'field_current',
			'label' => 'Кол-во полей',
			'type' => 'number',
		], [
			'name'  => 'field_max',
			'label' => 'Макс кол-во полей',
			'type' => 'number',
		], [
			'name'  => 'temp_min',
			'label' => 'Темп. мин.',
			'type' => 'number',
		], [
			'name'  => 'temp_max',
			'label' => 'Темп. макс.',
			'type' => 'number',
		], [
			'name'  => 'metal',
			'label' => 'Метал',
			'type' => 'number',
			'decimals' => 4,
			'thousands_sep' => ' ',
			'dec_point'     => ',',
		], [
			'name'  => 'crystal',
			'label' => 'Кристал',
			'type' => 'number',
			'decimals' => 4,
			'thousands_sep' => ' ',
			'dec_point'     => ',',
		], [
			'name'  => 'deuterium',
			'label' => 'Дейтерий',
			'type' => 'number',
			'decimals' => 4,
			'thousands_sep' => ' ',
			'dec_point'     => ',',
		], [
			'name'  => 'debris_metal',
			'label' => 'Поле обломков: Метал',
			'type' => 'number',
		], [
			'name'  => 'debris_crystal',
			'label' => 'Поле обломков: Кристал',
			'type' => 'number',
		]]);
	}

	public function setupCreateOperation()
	{
		$this->crud->setValidation(PlanetRequest::class);
		$this->crud->setTitle('Создание планеты');
		$this->crud->setSubheading('Создание планеты');

		$this->crud->addField([
			'name'       => 'name',
			'label'      => 'Название',
			'type'       => 'text',
			'default'	=> __('main.sys_colo_defaultname'),
		]);

		$this->crud->addField([
			'name'       => 'galaxy',
			'label'      => 'Галактика',
			'type'       => 'number',
		]);

		$this->crud->addField([
			'name'       => 'system',
			'label'      => 'Система',
			'type'       => 'number',
		]);

		$this->crud->addField([
			'name'       => 'planet',
			'label'      => 'Планета',
			'type'       => 'number',
		]);

		$this->crud->addField([
			'name'       => 'user_id',
			'label'      => 'Пользователь',
			'entity'    => 'user',
			'type'       => 'select',
			'attribute' => 'username',
			'model'     => User::class,
		]);
	}

	public function setupUpdateOperation()
	{
		$this->crud->setTitle('Редактирование планеты');
		$this->crud->setSubheading('Редактирование планеты');

		$this->crud->addField([
			'name'       => 'name',
			'label'      => 'Название',
			'type'       => 'text',
			'default'	=> __('main.sys_colo_defaultname'),
		]);

		$this->crud->addField([
			'name'       => 'galaxy',
			'label'      => 'Галактика',
			'type'       => 'number',
		]);

		$this->crud->addField([
			'name'       => 'system',
			'label'      => 'Система',
			'type'       => 'number',
		]);

		$this->crud->addField([
			'name'       => 'planet',
			'label'      => 'Планета',
			'type'       => 'number',
		]);

		$this->crud->addField([
			'name'       => 'planet_type',
			'label'      => 'Тип планеты',
			'type'	=> 'enum',
			'enum_class' => PlanetType::class,
			'enum_function' => 'title',
		]);

		$this->crud->addField([
			'name'       => 'user_id',
			'label'      => 'Пользователь',
			'entity'    => 'user',
			'type'       => 'select',
			'attribute' => 'username',
			'model'     => User::class,
		]);

		$this->crud->addField([
			'name'  => 'metal',
			'label' => 'Метал',
			'type' => 'number',
		]);

		$this->crud->addField([
			'name'  => 'crystal',
			'label' => 'Кристал',
			'type' => 'number',
		]);

		$this->crud->addField([
			'name'  => 'deuterium',
			'label' => 'Дейтерий',
			'type' => 'number',
		]);

		$this->crud->addField([
			'name'  => 'debris_metal',
			'label' => 'Поле обломков: Метал',
			'type' => 'number',
		]);

		$this->crud->addField([
			'name'  => 'debris_crystal',
			'label' => 'Поле обломков: Кристал',
			'type' => 'number',
		]);
	}

	public function store(Request $request)
	{
		$this->crud->applyConfigurationFromSettings('create');
		$this->crud->hasAccessOrFail('create');

		$this->crud->validateRequest();

		$fields = $this->crud->getStrippedSaveRequest($request);

		$planetId = (new Galaxy())->createPlanet(
			new Coordinates($fields['galaxy'], $fields['system'], $fields['planet']),
			$fields['user_id'],
			$fields['name']
		);

		if ($planetId) {
			Alert::success('Планета создана, id: ' . $planetId);
		} else {
			Alert::error('Не удалось создать планету');
		}

		$this->crud->setSaveAction();

		return $this->crud->performSaveAction($planetId);
	}
}
