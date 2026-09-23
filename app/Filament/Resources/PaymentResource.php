<?php

namespace App\Filament\Resources;

use App\Models\Payment;
use App\Filament\Resources\PaymentResource\Pages;
use Filament\Resources\Resource;

class PaymentResource extends Resource
{
	protected static ?string $model = Payment::class;

	protected static ?int $navigationSort = 40;

	public static function getModelLabel(): string
	{
		return __('admin.payments.transaction');
	}

	public static function getPluralModelLabel(): string
	{
		return __('admin.payments.transactions');
	}

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-banknotes';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.pages.payments');
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
