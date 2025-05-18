<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
	protected static ?string $model = Role::class;

	protected static ?string $navigationIcon = 'lucide-shield';
	protected static ?int $navigationSort = 20;
	protected static ?string $recordTitleAttribute = 'name';
	protected static ?string $slug = 'roles';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.settings');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.roles');
	}

	public static function getModelLabel(): string
	{
		return __('admin.roles.model');
	}

	public static function getPluralModelLabel(): string
	{
		return __('admin.roles.model_plural');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('roles');
	}

	public static function canGloballySearch(): bool
	{
		return false;
	}

	public static function form(Form $form): Form
	{
		return $form
			->schema([
				Section::make()
					->schema([
						TextInput::make('name')
							->label(__('admin.roles.form.code'))
							->required(),
						Select::make('guard_name')
							->label(__('admin.roles.form.guard'))
							->options([
								'web' => 'web',
								'api' => 'api',
							])
							->default('web')
							->required(),
						Select::make('permissions')
							->columnSpanFull()
							->multiple()
							->label(__('admin.roles.form.permissions'))
							->relationship(
								name: 'permissions',
								modifyQueryUsing: fn(Builder $query) => $query->orderBy('name'),
							)
							->getOptionLabelFromRecordUsing(fn(Permission $record) => ___('admin.roles.list.' . $record->name, $record->name) . " ({$record->name}, {$record->guard_name})")
							->searchable(['name', 'guard_name'])
							->preload(),
					]),
			]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('id')
					->label('ID')
					->searchable(),
				TextColumn::make('name')
					->label(__('admin.roles.table.code'))
					->searchable(),
				TextColumn::make('permissions_count')
					->label(__('admin.roles.table.count'))
					->counts('permissions'),
				TextColumn::make('guard_name')
					->label(__('admin.roles.table.guard'))
					->searchable(),
			])
			->actions([
				Tables\Actions\EditAction::make()
					->iconButton(),
			]);
	}

	public static function getPages(): array
	{
		return [
			'index' => ListRoles::route('/'),
			'create' => CreateRole::route('/create'),
			'edit' => EditRole::route('/{record}/edit'),
		];
	}
}
