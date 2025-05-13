<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Engine\Enums\MessageType;
use App\Exceptions\Exception;
use App\Filament\Resources\PaymentResource;
use App\Models\LogCredit;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\MessageNotification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

/**
 * @property Payment $record
 */
class CreatePayment extends CreateRecord
{
	protected static string $resource = PaymentResource::class;
	protected static ?string $title = 'Создать транзакцию';

	public function form(Form $form): Form
	{
		return $form
			->columns(1)
			->schema([
				Select::make('user_id')
					->label('Игрок')
					->relationship('user', 'username')
					->searchable(),
				TextInput::make('amount')
					->label('Сумма')
					->integer(),
			]);
	}

	protected function handleRecordCreation(array $data): Payment
	{
		$user = User::find($data['user_id']);

		if (!$user) {
			throw new Exception('Не удалось найти игрока');
		}

		/** @var Payment $record */
		$record = parent::handleRecordCreation($data);

		$user->increment('credits', (int) $data['amount']);

		LogCredit::create([
			'user_id' => $user->id,
			'amount' => (int) $data['amount'],
			'type' => 6,
		]);

		$user->notify(new MessageNotification(null, MessageType::System, 'Обработка платежей', 'На ваш счет зачислено ' . $data['amount'] . ' кредитов'));

		return $record;
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return 'Начисление ' . $this->record->amount . ' кредитов прошло успешно';
	}
}
