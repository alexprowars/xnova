<?php

namespace App\Engine\Enums;

use Filament\Support\Contracts\HasLabel;

enum MessageType: int implements HasLabel
{
	case Spy = 1;
	case User = 2;
	case Alliance = 3;
	case Battle = 4;
	case System = 5;
	case Fleet = 6;
	case Expedition = 15;
	case Queue = 99;

	public function title(): string
	{
		return __('messages.type.' . $this->value);
	}

	public function getLabel(): ?string
	{
		return $this->title();
	}
}
