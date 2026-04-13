<?php

namespace App;

class Helpers
{
	public static function convertIp($ip)
	{
		if (!is_numeric($ip)) {
			return sprintf("%u", ip2long($ip));
		}

		return long2ip($ip);
	}
}
