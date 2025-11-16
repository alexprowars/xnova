<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListPayments extends ListRecords
{
	protected static string $resource = PaymentResource::class;
	protected static ?string $title = 'Транзакции';

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
		];
	}

	public function table(Table $table): Table
	{
		return $table
			->emptyStateHeading('Транзакции не найдены')
			->columns([
				TextColumn::make('transaction_id')
					->label('ID')
					->sortable(),
				TextColumn::make('transaction_time')
					->label('Дата')
					->dateTime()
					->sortable(),
				TextColumn::make('method')
					->label('Метод')
					->sortable(),
				TextColumn::make('amount')
					->label('Сумма')
					->numeric()
					->sortable(),
				TextColumn::make('user.username')
					->label('Игрок')
					->numeric()
					->sortable(),
			]);
	}
}
