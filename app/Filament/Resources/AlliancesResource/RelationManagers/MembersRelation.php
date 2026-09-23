<?php

namespace App\Filament\Resources\AlliancesResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelation extends RelationManager
{
	protected static string $relationship = 'members';

	public static function getLabel(): ?string
	{
		return __('admin.alliances.members');
	}

	public static function getModelLabel(): ?string
	{
		return __('admin.alliances.member');
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(1)
			->schema([
				Select::make('user_id')
					->label(__('admin.common.user'))
					->relationship(name: 'user', titleAttribute: 'username')
					->searchable()
					->required(),
			]);
	}

	public function table(Table $table): Table
	{
		return $table
			->heading(__('admin.alliances.members'))
			->headerActions([
				CreateAction::make()
					->icon('lucide-circle-plus')
					->label(__('admin.alliances.add')),
			])
			->recordActions([
				DeleteAction::make()
					->iconButton()
					->modalHeading(__('admin.alliances.remove_member_heading'))
					->modalDescription(__('admin.alliances.remove_member_description')),
			])
			->defaultSort('id', 'desc')
			->columns([
				TextColumn::make('user_id')
					->label('ID'),
				TextColumn::make('rank')
					->label(__('admin.alliances.rank')),
				TextColumn::make('user.username')
					->label(__('admin.common.user')),
				TextColumn::make('user.email')
					->label('Email'),
				TextColumn::make('user.galaxy')
					->label(__('admin.common.galaxy')),
				TextColumn::make('user.system')
					->label(__('admin.common.system')),
				TextColumn::make('user.planet')
					->label(__('admin.common.planet')),
				TextColumn::make('created_at')
					->label(__('admin.alliances.added_at'))
					->dateTime(),
			]);
	}
}
