<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class UserLostPasswordSuccess extends Mailable
{
	protected $fields = [];

	public function __construct(array $fields)
	{
		$this->fields = $fields;
		$this->fields['#SERVER#'] = Request::instance()->getHttpHost();
	}

	public function build()
	{
		$this->subject(config('app.name') . ": Новый пароль");

		$template = File::get(resource_path('/views/email/remind_2.html'));
		$template = strtr($template, $this->fields);

		return $this->html($template);
	}
}
