<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
	protected static string $resource = RoleResource::class;

	public function getHeading(): string
	{
		return __('admin.navigation.pages.roles');
	}

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
		];
	}
}
