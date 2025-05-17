<?php

namespace App;

class Format
{
	public static function time($seconds, $separator = '')
	{
		$day    = floor($seconds / (24 * 3600));
		$hh     = floor($seconds / 3600 % 24);
		$mm     = floor($seconds / 60 % 60);
		$ss     = floor($seconds / 1 % 60);

		$time = '';

		if ($day != 0) {
			$time .= $day . (($separator != '') ? $separator : ' д. ');
		}

		if ($hh > 0) {
			$time .= $hh . (($separator != '') ? $separator : ' ч. ');
		}

		if ($mm > 0) {
			$time .= $mm . (($separator != '') ? $separator : ' мин. ');
		}

		if ($ss != 0) {
			$time .= $ss . (($separator != '') ? '' : ' с. ');
		}

		if (!$time) {
			$time = '-';
		}

		return trim($time);
	}

	public static function number($n)
	{
		if ($n > 1000000000) {
			return number_format(floor($n / 1000000), 0, ",", ".") . 'kk';
		}

		return number_format($n, 0, ",", ".");
	}

	public static function text($text)
	{
		$text = htmlspecialchars(str_replace("'", '&#39;', $text));
		$text = addslashes($text);
		$text = trim(nl2br(strip_tags($text, '<br>')));
		$text = str_replace(["\r\n", "\n", "\r"], '', $text);

		return $text;
	}
}
