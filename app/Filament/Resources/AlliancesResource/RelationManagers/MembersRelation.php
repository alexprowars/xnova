<?php

namespace App\Filament\Resources\AlliancesResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;

class MembersRelation extends RelationManager
{
	protected static string $relationship = 'members';

	public static function getLabel(): ?string
	{
		return 'Участники';
	}

	public static function getModelLabel(): ?string
	{
		return 'участник';
	}

	public function form(Form $form): Form
	{
		return $form
			->columns(1)
			->schema([
				Select::make('user_id')
					->label('Пользователь')
					->relationship(name: 'user', titleAttribute: 'username')
					->searchable()
					->required(),
			]);
	}

	public function table(Table $table): Table
	{
		return $table
			->heading('Участники')
			->headerActions([
				CreateAction::make()
					->icon('lucide-circle-plus')
					->label('Добавить'),
			])
			->actions([
				Tables\Actions\DeleteAction::make()
					->iconButton()
					->modalHeading('Удалить участника из альянса?')
					->modalDescription('После нажатия кнопки "подтвердить", выбранный вами участник выйдет из альянса'),
			])
			->defaultSort('id', 'desc')
			->columns([
				TextColumn::make('user_id')
					->label('ID'),
				TextColumn::make('rank')
					->label('Ранг'),
				TextColumn::make('user.username')
					->label('Пользователь'),
				TextColumn::make('user.email')
					->label('Email'),
				TextColumn::make('user.galaxy')
					->label('Галактика'),
				TextColumn::make('user.system')
					->label('Система'),
				TextColumn::make('user.planet')
					->label('Планета'),
				TextColumn::make('created_at')
					->label('Дата добавления')
					->dateTime(),
			]);
	}
}
