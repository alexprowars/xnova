<?php

namespace App\Filament\Resources;

use App\Models\Payment;
use App\Filament\Resources\PaymentResource\Pages;
use Filament\Resources\Resource;

class PaymentResource extends Resource
{
	protected static ?string $model = Payment::class;

	protected static ?int $navigationSort = 40;
	protected static ?string $modelLabel = 'Транзакции';
	protected static ?string $pluralModelLabel = 'Транзакции';

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-banknotes';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.payments');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('payments');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListPayments::route('/'),
			'create' => Pages\CreatePayment::route('/create'),
		];
	}
}
