<?php

namespace App;

class Socials
{
	private static $facebook_id;
	private static $facebook_access_token;
	private static $vk_api_secret;
	private static $vk_app_id;
	private static $vk_api_url = 'http://api.vk.com/api.php';
	private static $ok_api_url = '';
	private static $ok_api_key = '';

	public static function facebookConnect ($id = 'me', $access_token = '')
	{
		self::$facebook_id = $id;
		self::$facebook_access_token = $access_token;
	}

	public static function facebookLoad ($object = '', $params = array())
	{
		if (!self::$facebook_id || !self::$facebook_access_token)
			return false;

		$url = 'https://graph.facebook.com/'.self::$facebook_id.'/';

		if ($object != '')
			$url .= $object.'/';

		$url .= '?';

		if (self::$facebook_access_token != '')
			$url .= 'access_token='.self::$facebook_access_token.'';

		$url .= '&'.http_build_query($params);

		return json_decode(file_get_contents($url), true);
	}

	public static function vkConnect ($app_id, $api_secret)
	{
		self::$vk_app_id 		= $app_id;
		self::$vk_api_secret 	= $api_secret;
	}

	public static function vkLoad ($method, $params = array())
	{
		$params['api_id'] 		= self::$vk_app_id;
		$params['method'] 		= $method;
		$params['timestamp'] 	= time() + 100;
		$params['format'] 		= 'json';
		$params['random'] 		= rand(0,10000);

		ksort($params);

		$sig = '';
		foreach($params as $k => $v)
		{
			$sig .= trim($k).'='.trim($v);
		}

		$params['sig'] = md5($sig.self::$vk_api_secret);

		return json_decode(file_get_contents(self::$vk_api_url.'?'.http_build_query($params)), true);
	}

	public static function okConnect ($api_key, $api_url)
	{
		if (strpos($api_url, 'ok.ru') === false)
			$api_url = 'http://api.ok.ru/';

		self::$ok_api_url = $api_url;
		self::$ok_api_key = $api_key;
	}

	public static function okLoad ($method, $params = array(), $secret = '')
	{
		if (!is_array($params))
			$params = [];

		$params['application_key'] = self::$ok_api_key;
		$params['format'] = 'JSON';

		if (isset($params['session_secret_key']))
		{
			$signature = $params['session_secret_key'];
			unset($params['session_secret_key']);
		}
		else
			$signature = $secret;

		ksort($params);

		$sig = '';

		foreach($params as $k => $v)
			$sig .= $k.'='.$v;

		$sig .= $signature;

		$params['sig'] = md5($sig);

		$res = file_get_contents(self::$ok_api_url.'api/'.$method.'?'.http_build_query($params));

		return json_decode($res, true);
	}

	public static function smsGetToken ()
	{
		$ch = curl_init("http://sms.ru/auth/get_token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$token = curl_exec($ch);
		curl_close($ch);

		return $token;
	}

	public static function phoneFormat ($phone)
	{
		if ($phone == '')
			return '';

		$phone = str_replace(array('+', '-', '(', ')', ' '), '', $phone);

		if ($phone[0] == '8')
			$phone[0] = '7';

		if ($phone[0] != '7')
			$phone = '7'.$phone;

		if (mb_strlen($phone, 'UTF-8') == 11)
			return $phone;
		else
			return '';
	}

	public static function smsSend ($phone, $message, $token = '')
	{
		if (!$token)
			$token = self::smsGetToken();

		$phone = self::phoneFormat($phone);

		if (mb_strlen($phone, 'UTF-8') == 11)
		{
			$ch = curl_init("http://sms.ru/sms/send");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array(

				"login"		=>	SMS_LOGIN,
				"sig"		=>	md5(SMS_PASSWORD.$token),
				"token"		=>	$token,
				"from"		=> 	SMS_FROM,
				"api_id"	=>	SMS_APPID,
				"to"		=>	$phone,
				"text"		=>	$message

			));
			$sms = curl_exec($ch);
			curl_close($ch);

			return $sms;
		}

		return false;
	}
}

?>