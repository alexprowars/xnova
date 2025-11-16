<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewUsers extends ViewRecord
{
	protected static string $resource = UserResource::class;

	public function infolist(Schema $schema): Schema
	{
		return $schema
			->schema([
				TextEntry::make('id')
					->label('ID'),
				TextEntry::make('username')
					->label('Юзернэйм'),
				TextEntry::make('email')
					->label('Email'),
			])
			->columns(1);
	}
}
