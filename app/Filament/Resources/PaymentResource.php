<?php

namespace App\Filament\Resources;

use App\Models\Payment;
use App\Filament\Resources\PaymentResource\Pages;
use Filament\Resources\Resource;

class PaymentResource extends Resource
{
	protected static ?string $model = Payment::class;

	protected static ?string $navigationIcon = 'heroicon-o-banknotes';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Финансы';
	protected static ?int $navigationSort = 40;
	protected static ?string $modelLabel = 'Транзакции';
	protected static ?string $pluralModelLabel = 'Транзакции';

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListPayments::route('/'),
			'create' => Pages\CreatePayment::route('/create'),
		];
	}
}
