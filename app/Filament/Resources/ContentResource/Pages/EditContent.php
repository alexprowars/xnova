<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditContent extends EditRecord
{
	protected static string $resource = ContentResource::class;
	protected static ?string $title = 'Редактирование записи';

	public function form(Form $form): Form
	{
		return $form
			->schema([
				TextInput::make('title')
					->label('Название'),
				TextInput::make('alias')
					->label('Символьный код'),
				RichEditor::make('html')
					->label('Контент'),
			])
			->columns(1);
	}
}
