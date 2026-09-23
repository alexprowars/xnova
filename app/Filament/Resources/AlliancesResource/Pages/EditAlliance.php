<?php

namespace App\Filament\Resources\AlliancesResource\Pages;

use App\Filament\Resources\AlliancesResource;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditAlliance extends EditRecord
{
	protected static string $resource = AlliancesResource::class;

	public function getTitle(): string
	{
		return __('admin.alliances.edit_alliance');
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make()
					->schema([
						TextInput::make('name')
							->label(__('admin.common.name'))
							->required(),
						TextInput::make('tag')
							->label(__('admin.alliances.tag'))
							->required(),
						Select::make('user_id')
							->label(__('admin.alliances.leader'))
							->relationship('user', 'username')
							->native(false)
							->searchable(['id', 'username', 'email']),
						TextInput::make('web')
							->label(__('admin.alliances.website')),
						RichEditor::make('description')
							->label(__('admin.alliances.description')),
						SpatieMediaLibraryFileUpload::make('photo')
							->label(__('admin.alliances.logo')),
					]),
			]);
	}
}
