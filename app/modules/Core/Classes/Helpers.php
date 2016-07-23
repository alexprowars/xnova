<?php

namespace Friday\Core;

class Helpers
{
	public static function phoneFormat ($phone)
	{
		if ($phone == '')
			return '';

		$phone = str_replace(['+', '-', '(', ')', ' '], '', $phone);

		return $phone;
	}

	public static function pastTimeFormat ($time, $currentTime = 0)
	{
		if (!$currentTime)
			$currentTime = time();

		$delta = $currentTime - $time;

		if ($delta < 60)
			return 'только что';
		elseif ($delta < 3600)
			return floor($delta / 60).' мин.';
		elseif ($delta < 86400)
			return floor($delta / 3600).' ч.';
		else
			return floor($delta / 86400).' дн.';
	}

	public static function getPlural ($n = 0, array $forms = [])
	{
		return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
	}

	public static function isPhone ($string = '')
	{
		return (!preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/iu', $string) ? false : true);
	}
}

?>