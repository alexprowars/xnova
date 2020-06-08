<?php

namespace Xnova\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Prologue\Alerts\Facades\Alert;
use Xnova\Entity\Coordinates;
use Xnova\Galaxy;
use Backpack\CRUD\app\Http\Controllers\Operations;
use Xnova\Http\Requests\Admin\MoonRequest;
use Xnova\Models\Planet;

/**
 * @property CrudPanel $crud
 */
/** @noinspection PhpUnused */
class MoonsController extends CrudController
{
	use Operations\ListOperation;
	use Operations\CreateOperation;
	use Operations\ShowOperation;
	use Operations\DeleteOperation;

	public static function getMenu()
	{
		return [[
			'code'	=> 'moons',
			'title' => 'Список лун',
			'url'	=> backpack_url('moons'),
			'icon'	=> 'moon-o',
			'sort'	=> 100
		]];
	}

	public function setup()
	{
		$this->crud->setModel(Planet::class);
		$this->crud->setEntityNameStrings('луну', 'луны');
		$this->crud->setRoute(backpack_url('moons'));

		$this->crud->operation('list', function () {
			$this->crud->orderBy('id', 'desc');
			$this->crud->addClause('where', 'planet_type', '=', 3);
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
				],
			]);
		});

		$this->crud->operation('create', function () {
			$this->crud->setValidation(MoonRequest::class);
			$this->crud->setTitle('Создание луны');
			$this->crud->setSubheading('Создание луны');

			$this->crud->addField([
				'name'	=> 'galaxy',
				'label'	=> 'Галактика',
				'type'	=> 'number',
			]);

			$this->crud->addField([
				'name'	=> 'system',
				'label'	=> 'Система',
				'type'	=> 'number',
			]);

			$this->crud->addField([
				'name'	=> 'planet',
				'label'	=> 'Планета',
				'type'	 => 'number',
			]);

			$this->crud->addField([
				'name'	=> 'id_owner',
				'label'	=> 'Пользователь',
				'type'	=> 'number',
			]);

			$this->crud->addField([
				'name'	=> 'diameter',
				'label'	=> 'Диаметр',
				'type'	=> 'number',
				'default' => 1,
			]);
		});
	}

	public function store()
	{
		$this->crud->applyConfigurationFromSettings('create');
		$this->crud->hasAccessOrFail('create');

		$this->crud->validateRequest();

		$fields = $this->crud->getStrippedSaveRequest();

		$diameter = min(max($fields['diameter'], 20), 0);

		$planetId = (new Galaxy())->createMoon(
			new Coordinates($fields['galaxy'], $fields['system'], $fields['planet']),
			$fields['id_owner'],
			$diameter
		);

		if ($planetId) {
			Alert::success('Луна создана, id: ' . $planetId);
		} else {
			Alert::error('Не удалось создать луну');
		}

		$this->crud->setSaveAction();

		return $this->crud->performSaveAction($planetId);
	}
}
