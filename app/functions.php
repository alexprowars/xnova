<?php

if (!function_exists('___')) {
	function ___(?string $key = null, ?string $default = null, array $replace = [], ?string $locale = null): ?string
	{
		if (is_null($key)) {
			return $key;
		}

		if (app('translator')->has($key, $locale)) {
			return trans($key, $replace, $locale);
		} else {
			return $default;
		}
	}
}
