<?php

namespace App\Filament;

use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

trait HasPageForm
{
	protected function getFormActions(): array
	{
		return [];
	}

	public function content(Schema $schema): Schema
	{
		return $schema
			->components([
				Form::make([EmbeddedSchema::make('form')])
					->id('form')
					->footer([
						Actions::make($this->getFormActions())
							->alignment($this->getFormActionsAlignment())
							->fullWidth($this->hasFullWidthFormActions())
							->sticky($this->areFormActionsSticky())
					])
			]);
	}
}
