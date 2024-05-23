<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Prologue\Alerts\Facades\Alert;
use App\Http\Requests\Admin\PaymentRequest;
use App\Models\LogCredit;
use App\Models\Payment;
use App\User;
use Backpack\CRUD\app\Http\Controllers\Operations;

/**
 * @property CrudPanel $crud
 */
class PaymentsController extends CrudController
{
	use Operations\ListOperation;
	use Operations\CreateOperation;
	use Operations\ShowOperation;

	public static function getMenu()
	{
		return [[
			'code'	=> 'payments',
			'title' => 'Финансы',
			'url'	=> backpack_url('payments'),
			'icon'	=> 'money',
			'sort'	=> 40,
		]];
	}

	public function setup()
	{
		$this->crud->setModel(Payment::class);
		$this->crud->setEntityNameStrings('транзакцию', 'транзакции');
		$this->crud->setRoute(backpack_url('payments'));
		$this->crud->setTitle('Транзакции');

		$this->crud->operation('list', function () {
			$this->crud->orderBy('id', 'desc');
			$this->crud->enableExportButtons();

			$this->crud->setColumns([[
					'name'  => 'transaction_id',
					'label' => 'transaction_id',
				], [
					'name'  => 'transaction_time',
					'label' => 'transaction_time',
				], [
					'name'  => 'method',
					'label' => 'method',
				], [
					'name'  => 'amount',
					'label' => 'amount',
				], [
					'label' => 'username',
					'type' => 'select',
					'name' => 'user',
					'entity' => 'user',
					'attribute' => 'username',
					'model' => User::class
				],
			]);
		});

		$this->crud->operation('create', function () {
			$this->crud->setValidation(PaymentRequest::class);
			$this->crud->setTitle('Начисление кредитов');

			$this->crud->addField([
				'name'       => 'name',
				'label'      => 'Логин или ID игрока',
				'type'       => 'text',
			]);

			$this->crud->addField([
				'name'       => 'amount',
				'label'      => 'Сумма',
				'type'       => 'number',
			]);
		});
	}

	public function store()
	{
		$this->crud->applyConfigurationFromSettings('create');
		$this->crud->hasAccessOrFail('create');

		$this->crud->validateRequest();

		$fields = $this->crud->getStrippedSaveRequest();

		$checkUser = User::query();

		if (is_numeric($fields['name'])) {
			$checkUser->where('id', (int) $fields['name']);
		} else {
			$checkUser->where('username', addslashes($fields['name']));
		}

		$checkUser = $checkUser->first(['id']);

		if (!$checkUser) {
			Alert::error('Не удалось создать планету');
		} else {
			User::query()->where('id', $checkUser->id)->increment('credits', (int) $fields['amount']);

			LogCredit::create([
				'user_id' => $checkUser->id,
				'amount' => (int) $fields['amount'],
				'type' => 6,
			]);

			User::sendMessage($checkUser->id, 0, 0, 2, 'Обработка платежей', 'На ваш счет зачислено ' . $fields['amount'] . ' кредитов');

			Alert::success('Начисление ' . $fields['amount'] . ' кредитов прошло успешно');
		}

		$this->crud->setSaveAction();

		return $this->crud->performSaveAction();
	}
}
