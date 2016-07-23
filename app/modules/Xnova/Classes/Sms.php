<?php

namespace Xnova;

use Phalcon\Di;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class Sms
{
	private $token = '';

	public function getToken ()
	{
		$ch = curl_init("http://sms.ru/auth/get_token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$token = curl_exec($ch);
		curl_close($ch);

		$this->token = $token;
	}

	public function send ($phone, $message)
	{
		if (!$this->token)
			$this->getToken();

		$phone = Helpers::phoneFormat($phone);

		if (mb_strlen($phone, 'UTF-8') == 11)
		{
			$config = Di::getDefault()->getShared('config');

			$ch = curl_init("http://sms.ru/sms/send");

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_POSTFIELDS, [
				"login"		=>	$config->sms->login,
				"sig"		=>	md5($config->sms->password.$this->token),
				"token"		=>	$this->token,
				"from"		=> 	$config->sms->from,
				"api_id"	=>	$config->sms->id,
				"to"		=>	$phone,
				"text"		=>	$message

			]);

			$sms = curl_exec($ch);
			curl_close($ch);

			return $sms;
		}

		return false;
	}
}