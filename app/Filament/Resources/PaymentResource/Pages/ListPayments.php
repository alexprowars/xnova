<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions;

class ListPayments extends ListRecords
{
	protected static string $resource = PaymentResource::class;
	protected static ?string $title = 'Транзакции';

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
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
					->dateTime('d.m.Y H:i:s')
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
