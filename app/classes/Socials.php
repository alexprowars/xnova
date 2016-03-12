<?php
namespace App;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

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

	public static function facebookLoad ($object = '', $params = [])
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

	public static function vkLoad ($method, $params = [])
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

	public static function okLoad ($method, $params = [], $secret = '')
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
}