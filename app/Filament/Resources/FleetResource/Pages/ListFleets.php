<?php

namespace App\Filament\Resources\FleetResource\Pages;

use App\Engine\Enums\PlanetType;
use App\Filament\Resources\FleetResource;
use App\Models\Fleet as FleetModel;
use App\Engine\Fleet;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListFleets extends ListRecords
{
	protected static string $resource = FleetResource::class;

	public function table(Table $table): Table
	{
		return $table
			->defaultSort('end_date')
			->paginated(false)
			->emptyStateHeading(__('admin.fleets.fleets_not_found'))
			->columns([
				TextColumn::make('id')
					->label('ID')
					->sortable(),
				TextColumn::make('mission')
					->label(__('admin.fleets.mission'))
					->html()
					->formatStateUsing(fn(FleetModel $record) =>
						Fleet::createFleetPopupedMissionLink($record, $record->mission->title(), '')
						. ' ' . ($record->mess == 1 ? 'R' : 'A'))
					->sortable(),
				TextColumn::make('entities')
					->label(__('admin.fleets.fleet_composition'))
					->html()
					->formatStateUsing(fn(FleetModel $record) => Fleet::createFleetPopupedFleetLink($record)),
				TextColumn::make('user_name')
					->label(__('admin.fleets.owner'))
					->formatStateUsing(fn(FleetModel $record) => '[' . $record->user_id . '] ' . $record->user_name)
					->sortable(),
				TextColumn::make('start_galaxy')
					->label(__('admin.fleets.origin'))
					->html()
					->formatStateUsing(fn(FleetModel $record) => '[' . $record->start_galaxy . ':' . $record->start_system . ':' . $record->start_planet . '] ' . (($record->start_type == PlanetType::PLANET) ? '[P]' : (($record->start_type == PlanetType::DEBRIS) ? 'D' : 'L'))),
				TextColumn::make('start_date')
					->label(__('admin.fleets.departure'))
					->dateTime('H:i:s d.m')
					->sortable(),
				TextColumn::make('target_user_id')
					->label(__('admin.fleets.target_player'))
					->html()
					->formatStateUsing(fn(FleetModel $record) => !empty($record->target_user_id) ? '[' . $record->target_user_id . '] ' . $record->target_user_name : '')
					->sortable(),
				TextColumn::make('end_galaxy')
					->label(__('admin.fleets.destination'))
					->html()
					->formatStateUsing(fn(FleetModel $record) => '[' . $record->end_galaxy . ':' . $record->end_system . ':' . $record->end_planet . '] ' . (($record->end_type == PlanetType::PLANET) ? '[P]' : (($record->end_type == PlanetType::DEBRIS) ? 'D' : 'L'))),
				TextColumn::make('end_date')
					->label(__('admin.fleets.arrival'))
					->dateTime('H:i:s d.m')
					->sortable(),
		]);
	}
}
