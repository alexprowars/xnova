<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class AcsRequestMessage extends AbstractMessage
{
	protected string $type = 'AcsRequest';

	public function getSubject(): ?string
	{
		return 'Флот';
	}

	public function render(): string
	{
		return 'Игрок ' . $this->data['user'] . ' приглашает вас произвести совместное нападение на планету ' . $this->data['planet']['name'] . ' ' . Coordinates::fromArray($this->data['planet'])->getLink() . ' игрока ' . $this->data['planet']['user'] . '. Имя ассоциации: ' . $this->data['assault'] . '. Если вы отказываетесь, то просто проигнорируйте данной сообщение.';
	}
}
