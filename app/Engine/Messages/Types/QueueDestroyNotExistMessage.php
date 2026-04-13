<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class QueueDestroyNotExistMessage extends AbstractMessage
{
	protected string $type = 'QueueDestroyNotExist';

	public function getSubject(): ?string
	{
		return __('main.sys_buildlist');
	}

	public function render(): string
	{
		return __('main.sys_nomore_level', ['item' => __('main.tech.' . $this->data['object'])]);
	}
}
