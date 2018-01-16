<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class Request
{
	private static $data = [];
	private static $status = true;

	public static function setStatus ($status = false)
	{
		if (!is_bool($status))
			$status = false;

		self::$status = $status;
	}

	public static function getStatus ()
	{
		return self::$status;
	}

	public static function setData ($data = [])
	{
		if (is_array($data))
			self::$data = $data;
	}

	public static function addData ($key, $value)
	{
		self::$data[$key] = $value;
	}

	public static function getData ()
	{
		return self::$data;
	}

	public static function getDataItem ($key)
	{
		return isset(self::$data[$key]) ? self::$data[$key] : false;
	}

	public static function clearData ()
	{
		self::$data = [];
	}
}