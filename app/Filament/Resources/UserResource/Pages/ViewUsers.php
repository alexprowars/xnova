<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUsers extends ViewRecord
{
	protected static string $resource = UserResource::class;

	public function infolist(Infolist $infolist): Infolist
	{
		return $infolist
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
