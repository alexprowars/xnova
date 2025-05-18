<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
	protected static string $resource = RoleResource::class;

	public function getHeading(): string
	{
		return __('admin/permissions.edit_title');
	}

	public function getHeaderActions(): array
	{
		return [
			DeleteAction::make(),
		];
	}
}
