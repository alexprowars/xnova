<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class NewLevelMessage extends AbstractMessage
{
	protected string $type = 'NewLevel';

	public function render(): string
	{
		if ($this->data['type'] == 'mine') {
			return '<a href="/officier">Получен новый промышленный уровень</a>';
		}

		if ($this->data['type'] == 'raid') {
			return '<a href="/officier">Получен новый военный уровень</a>';
		}

		return '';
	}
}
