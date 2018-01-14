<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class Format
{
	static function time ($seconds, $separator = '')
	{
		$day    = floor($seconds / (24 * 3600));
		$hh     = floor($seconds / 3600 % 24);
		$mm     = floor($seconds / 60 % 60);
		$ss     = floor($seconds / 1 % 60);

		$time = '';

		if ($day != 0)
			$time .= $day.(($separator != '') ? $separator : ' д. ');

		if ($hh > 0)
			$time .= $hh.(($separator != '') ? $separator : ' ч. ');

		if ($mm > 0)
			$time .= $mm.(($separator != '') ? $separator : ' мин. ');

		if ($ss != 0)
			$time .= $ss.(($separator != '') ? '' : ' с. ');

		if (!$time)
			$time = '-';

		return $time;
	}

	static function number ($n)
	{
		if ($n > 1000000000)
			return number_format(floor($n / 1000000), 0, ",", ".").'kk';

		return number_format($n, 0, ",", ".");
	}

	static function phone ($phone)
	{
		$phone = Helpers::phoneFormat($phone);

		if ($phone != '')
			$phone = preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "8 ($2) $3-$4-$5", $phone);

		return $phone;
	}

	static function text ($text)
	{
		$text = htmlspecialchars(str_replace("'", '&#39;', $text));
		$text = addslashes($text);
		$text = trim ( nl2br ( strip_tags ( $text, '<br>' ) ) );
		$text = str_replace(["\r\n", "\n", "\r"], '', $text);

		return $text;
	}

	static function bytes ($size)
	{
	     $units = [' B', ' KiB', ' MiB', ' GiB', ' TiB'];

	     for ($i = 0; $size >= 1024 && $i < 4; $i++)
			 $size /= 1024;

	     return round($size, 2).$units[$i];
	}
}