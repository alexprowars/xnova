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

	public function getTitle(): string
	{
		return __('admin.payments.transactions');
	}

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
		];
	}

	public function table(Table $table): Table
	{
		return $table
			->emptyStateHeading(__('admin.payments.transactions_not_found'))
			->columns([
				TextColumn::make('transaction_id')
					->label('ID')
					->sortable(),
				TextColumn::make('transaction_time')
					->label(__('admin.common.date'))
					->dateTime()
					->sortable(),
				TextColumn::make('method')
					->label(__('admin.payments.method'))
					->sortable(),
				TextColumn::make('amount')
					->label(__('admin.payments.amount'))
					->numeric()
					->sortable(),
				TextColumn::make('user.username')
					->label(__('admin.common.player'))
					->numeric()
					->sortable(),
			]);
	}
}
