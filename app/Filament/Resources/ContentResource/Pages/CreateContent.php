<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
	protected static string $resource = ContentResource::class;
	protected static ?string $title = 'Создать запись';
}
