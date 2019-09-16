<?php

namespace Xnova\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class UserDelete extends Mailable
{
	protected $fields = [];

    public function __construct (array $fields)
    {
		$this->fields = $fields;
		$this->fields['#SERVER#'] = Request::instance()->getHttpHost();
    }

    public function build ()
    {
		$this->from(Config::get('settings.email_notify'), Config::get('settings.site_title'))
			->subject(Config::get('settings.site_title').': Уведомление об удалении аккаунта: ' . Config::get('settings.universe') . ' вселенная');

		$template = File::get(resource_path('/views/email/delete.html'));
		$template = strtr($template, $this->fields);

		return $this->html($template);
    }
}
