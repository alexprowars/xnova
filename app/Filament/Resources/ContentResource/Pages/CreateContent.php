<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
	protected static string $resource = ContentResource::class;

	public function getTitle(): string
	{
		return __('admin.content.create_record');
	}
}
