<?php

namespace App\Filament\Components\Form\Fields;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns\CanBeAutofocused;
use Filament\Forms\Components\Concerns\CanBeMarkedAsRequired;
use Filament\Forms\Components\Concerns\CanBeValidated;
use Filament\Forms\Components\Concerns\HasExtraFieldWrapperAttributes;
use Filament\Forms\Components\Concerns\HasHelperText;
use Filament\Forms\Components\Concerns\HasHint;
use Filament\Forms\Components\Concerns\HasName;
use Illuminate\Contracts\Support\Htmlable;

class Group extends Component
{
	use CanBeAutofocused;
	use CanBeMarkedAsRequired;
	use CanBeValidated;
	use HasExtraFieldWrapperAttributes;
	use HasHelperText;
	use HasHint;
	use HasName;

	protected string $view = 'filament.components.form.group';
	protected string $viewIdentifier = 'field';

	final public function __construct(string|array|Htmlable|Closure|null $label = null)
	{
		is_array($label)
			? $this->schema($label)
			: $this->label($label);
	}

	public static function make(string|array|Htmlable|Closure|null $label = null): static
	{
		$static = app(static::class, ['label' => $label]);
		$static->configure();

		return $static;
	}
}
