<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\EditRecord;

class EditContent extends EditRecord
{
	protected static string $resource = ContentResource::class;
	protected static ?string $title = 'Редактирование записи';
}
