<?php

namespace Xnova\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class UserRegistration extends Mailable
{
	protected $fields = [];

	public function __construct(array $fields)
	{
		$this->fields = $fields;
		$this->fields['#SERVER#'] = Request::instance()->getHttpHost();
	}

	public function build()
	{
		$this->from(config('settings.email_notify'), config('settings.site_title'))
			->subject(config('settings.site_title') . ": Регистрация");

		$template = File::get(resource_path('/views/email/registration.html'));
		$template = strtr($template, $this->fields);

		return $this->html($template);
	}
}
