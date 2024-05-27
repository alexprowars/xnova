<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Prologue\Alerts\Facades\Alert;
use App\Entity\Coordinates;
use App\Galaxy;
use App\Http\Requests\Admin\PlanetRequest;
use App\Models\Planet;
use Backpack\CRUD\app\Http\Controllers\Operations;

/**
 * @property CrudPanel $crud
 */
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
			'icon'	=> 'globe',
			'sort'	=> 80
		]];
	}

	public function setup()
	{
		$this->crud->setModel(Planet::class);
		$this->crud->setEntityNameStrings('планету', 'планеты');
		$this->crud->setRoute(backpack_url('planets'));

		$this->crud->operation('list', function () {
			$this->crud->orderBy('id', 'desc');
			//$this->crud->addClause('where', 'planet_type', '=', 1);
			$this->crud->enableExportButtons();

			$this->crud->setColumns([
				[
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
					'type'	=> 'select_from_array',
					'options'	=> __('main.type_planet'),
				], [
					'name'  => 'last_update',
					'label' => 'Время обновления',
					'type'	=> 'closure',
					'function' => function ($entry) {
						return $entry->last_update?->format('d.m.Y H:i:s');
					},
				],
			]);
		});

		$this->crud->operation(['create', 'update'], function () {
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
				'type'       => 'number',
			]);
		});

		$this->crud->operation('update', function () {
			$this->crud->setTitle('Редактирование планеты');
			$this->crud->setSubheading('Редактирование планеты');
		});
	}

	public function store()
	{
		$this->crud->applyConfigurationFromSettings('create');
		$this->crud->hasAccessOrFail('create');

		$this->crud->validateRequest();

		$fields = $this->crud->getStrippedSaveRequest();

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
