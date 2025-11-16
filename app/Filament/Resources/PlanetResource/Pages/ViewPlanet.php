<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Filament\Resources\PlanetResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ViewPlanet extends ViewRecord
{
	protected static string $resource = PlanetResource::class;

	public function infolist(Schema $schema): Schema
	{
		return $schema
			->schema([
				TextEntry::make('id')
					->label('ID'),
				TextEntry::make('name')
					->label('Название'),
				TextEntry::make('user_id')
					->label('Игрок'),
				Fieldset::make('Координаты')
					->columns(4)
					->schema([
						TextEntry::make('galaxy')
							->label('Галактика'),
						TextEntry::make('system')
							->label('Система'),
						TextEntry::make('planet')
							->label('Планета'),
						TextEntry::make('planet_type')
							->label('Тип'),
					]),
				TextEntry::make('last_update')
					->dateTime()
					->label('Время обновления'),
				TextEntry::make('last_active')
					->dateTime()
					->label('Время активности'),
				TextEntry::make('destroyed_at')
					->dateTime()
					->label('Время уничтожения'),
				TextEntry::make('merchand')
					->dateTime()
					->label('Время покупки ресурсов'),
				TextEntry::make('image')
					->label('Картинка'),
				TextEntry::make('diameter')
					->label('Диаметр'),
				TextEntry::make('field_current')
					->label('Кол-во полей'),
				TextEntry::make('field_max')
					->label('Макс кол-во полей'),
				TextEntry::make('temp_min')
					->label('Темп. мин.'),
				TextEntry::make('temp_max')
					->label('Темп. макс.'),
				Fieldset::make('Ресурсы')
					->columns(3)
					->schema([
						TextEntry::make('metal')
							->label('Металл')
							->numeric(4, ',', ' '),
						TextEntry::make('crystal')
							->label('Кристалл')
							->numeric(4, ',', ' '),
						TextEntry::make('deuterium')
							->label('Дейтерий')
							->numeric(4, ',', ' '),
					]),
				Fieldset::make('Поле обломков')
					->schema([
						TextEntry::make('debris_metal')
							->label('Металл'),
						TextEntry::make('debris_crystal')
							->label('Кристалл'),
					]),
			])
			->columns(1);
	}
}
