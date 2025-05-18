<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Filament\Resources\PlanetResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists;

class ViewPlanet extends ViewRecord
{
	protected static string $resource = PlanetResource::class;

	public function infolist(Infolist $infolist): Infolist
	{
		return $infolist
			->schema([
				Infolists\Components\TextEntry::make('id')
					->label('ID'),
				Infolists\Components\TextEntry::make('name')
					->label('Название'),
				Infolists\Components\TextEntry::make('user_id')
					->label('Игрок'),

				Infolists\Components\Fieldset::make('Координаты')
					->columns(4)
					->schema([
						Infolists\Components\TextEntry::make('galaxy')
							->label('Галактика'),
						Infolists\Components\TextEntry::make('system')
							->label('Система'),
						Infolists\Components\TextEntry::make('planet')
							->label('Планета'),
						Infolists\Components\TextEntry::make('planet_type')
							->label('Тип'),
					]),

				Infolists\Components\TextEntry::make('last_update')
					->dateTime()
					->label('Время обновления'),
				Infolists\Components\TextEntry::make('last_active')
					->dateTime()
					->label('Время активности'),
				Infolists\Components\TextEntry::make('destruyed_at')
					->dateTime()
					->label('Время уничтожения'),
				Infolists\Components\TextEntry::make('merchand')
					->dateTime()
					->label('Время покупки ресурсов'),
				Infolists\Components\TextEntry::make('image')
					->label('Картинка'),
				Infolists\Components\TextEntry::make('diameter')
					->label('Диаметр'),
				Infolists\Components\TextEntry::make('field_current')
					->label('Кол-во полей'),
				Infolists\Components\TextEntry::make('field_max')
					->label('Макс кол-во полей'),
				Infolists\Components\TextEntry::make('temp_min')
					->label('Темп. мин.'),
				Infolists\Components\TextEntry::make('temp_max')
					->label('Темп. макс.'),

				Infolists\Components\Fieldset::make('Ресурсы')
					->columns(3)
					->schema([
						Infolists\Components\TextEntry::make('metal')
							->label('Металл')
							->numeric(4, ',', ' '),
						Infolists\Components\TextEntry::make('crystal')
							->label('Кристалл')
							->numeric(4, ',', ' '),
						Infolists\Components\TextEntry::make('deuterium')
							->label('Дейтерий')
							->numeric(4, ',', ' '),
					]),
				Infolists\Components\Fieldset::make('Поле обломков')
					->schema([
						Infolists\Components\TextEntry::make('debris_metal')
							->label('Металл'),
						Infolists\Components\TextEntry::make('debris_crystal')
							->label('Кристалл'),
					]),
			])
			->columns(1);
	}
}
