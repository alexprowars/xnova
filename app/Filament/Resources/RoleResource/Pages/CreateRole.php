<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
	protected static string $resource = RoleResource::class;

	public function getHeading(): string
	{
		return __('admin/permissions.create_title');
	}
}
