<?php

namespace App\Engine\Messages;

interface MessageContract
{
	public function render(): string;

	public function toArray(): array;
}
