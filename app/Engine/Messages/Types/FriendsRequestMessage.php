<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class FriendsRequestMessage extends AbstractMessage
{
	protected string $type = 'MissionAttack';

	public function getSubject(): ?string
	{
		return 'Запрос дружбы';
	}

	public function render(): string
	{
		return 'Игрок ' . $this->data['name'] . ' отправил вам запрос на добавление в друзья. <a href="/friends/requests"><< просмотреть >></a>';
	}
}
