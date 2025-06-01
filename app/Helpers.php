<?php

namespace App;

use Illuminate\Support\Facades\URL;

class Helpers
{
	public static function checkString($str, $cut = false)
	{
		if ($cut) {
			$str = strip_tags($str);
		}

		return htmlspecialchars($str);
	}

	public static function is_email($email)
	{
		return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function buildPlanetAdressLink($CurrentPlanet)
	{
		$uri = URL::to('galaxy/' . $CurrentPlanet['galaxy'] . '/' . $CurrentPlanet['system']);

		return '<a href="' . $uri . '">[' . $CurrentPlanet['galaxy'] . ':' . $CurrentPlanet['system'] . ':' . $CurrentPlanet['planet'] . ']</a>';
	}

	public static function isMobile(): bool
	{
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			return false;
		}

		return preg_match('/\b(?:a(?:ndroid|vantgo)|b(?:lackberry|olt|o?ost)|cricket|docomo|hiptop|i(?:emobile|p[ao]d)|kitkat|m(?:ini|obi)|palm|(?:i|smart|windows )phone|symbian|up\.(?:browser|link)|tablet(?: browser| pc)|(?:hp-|rim |sony )tablet|w(?:ebos|indows ce|os))/i', $_SERVER['HTTP_USER_AGENT']);
	}

	public static function convertIp($ip)
	{
		if (!is_numeric($ip)) {
			return sprintf("%u", ip2long($ip));
		} else {
			return long2ip($ip);
		}
	}
}
