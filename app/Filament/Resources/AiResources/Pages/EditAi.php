<?php

namespace App\Filament\Resources\AiResources\Pages;

use App\Engine\Ai\StrategyType;
use App\Filament\Resources\AiResources;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditAi extends EditRecord
{
	protected static string $resource = AiResources::class;

	public function getTitle(): string
	{
		return __('admin.ai.edit_bot');
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make()
					->schema([
						Toggle::make('active')
							->label(__('admin.common.activity')),
						TextEntry::make('user')
							->label(__('admin.common.player'))
							->formatStateUsing(function (?User $state) {
								if (!$state) {
									return null;
								}

								return '[' . $state->id . '] ' . $state->username . ' [' . $state->galaxy . ':' . $state->system . ':' . $state->planet . ']';
							}),
						Select::make('strategy')
							->label(__('admin.ai.strategy'))
							->options(StrategyType::class),
					]),
			]);
	}
}
