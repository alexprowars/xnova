<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class UserDelete extends Mailable
{
	protected $fields = [];

	public function __construct(array $fields)
	{
		$this->fields = $fields;
		$this->fields['#SERVER#'] = Request::instance()->getHttpHost();
	}

	public function build()
	{
		$this->subject(config('app.name') . ': Уведомление об удалении аккаунта: ' . config('game.universe') . ' вселенная');

		$template = File::get(resource_path('/views/email/delete.html'));
		$template = strtr($template, $this->fields);

		return $this->html($template);
	}
}
