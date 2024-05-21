<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use App\Http\Requests\Admin\ContentRequest;
use App\Models\Content;
use Backpack\CRUD\app\Http\Controllers\Operations;

/**
 * @property CrudPanel $crud
 */
class ContentsController extends CrudController
{
	use Operations\ListOperation;
	use Operations\UpdateOperation;
	use Operations\CreateOperation;
	use Operations\ShowOperation;
	use Operations\DeleteOperation;

	public static function getMenu()
	{
		return [[
			'code'	=> 'contents',
			'title' => 'Контент',
			'url'	=> backpack_url('contents'),
			'icon'	=> 'pencil',
			'sort'	=> 190
		], [
			'code'	=> null,
			'title' => 'Администрирование',
			'icon'	=> '',
			'sort'	=> 200
		]];
	}

	public function setup()
	{
		$this->crud->setModel(Content::class);
		$this->crud->setEntityNameStrings('контент', 'контент');
		$this->crud->setRoute(backpack_url('contents'));

		$this->crud->operation('list', function () {
			$this->crud->orderBy('id', 'desc');
			$this->crud->enableExportButtons();

			$this->crud->setColumns([
				[
					'name'  => 'id',
					'label' => 'ID',
				], [
					'name'  => 'title',
					'label' => 'Название',
				], [
					'name'  => 'code',
					'label' => 'Символьный код',
				],
			]);
		});

		$this->crud->operation(['create', 'update'], function () {
			$this->crud->setValidation(ContentRequest::class);
			$this->crud->setTitle('Создание страницы');
			$this->crud->setSubheading('Создание страницы');

			$this->crud->addField([
				'name'       => 'title',
				'label'      => 'Название',
				'type'       => 'text',
			]);

			$this->crud->addField([
				'name'       => 'code',
				'label'      => 'Символьный код',
				'type'       => 'text',
			]);

			$this->crud->addField([
				'name'       => 'html',
				'label'      => 'Контент',
				'type'       => 'ckeditor',
			]);
		});

		$this->crud->operation('update', function () {
			$this->crud->setTitle('Редактирование страницы');
			$this->crud->setSubheading('Редактирование страницы');
		});
	}
}
