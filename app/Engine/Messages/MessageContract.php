<?php

namespace App\Engine\Messages;

interface MessageContract
{
	public function format(array $message): string;
}
