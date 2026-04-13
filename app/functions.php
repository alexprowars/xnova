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

if (!function_exists('log_var')) {
	function log_var(string $name, mixed $value): void
	{
		if (is_array($value)) {
			$value = var_export($value, true);
		}

		log_comment("$name = $value");
	}
}

if (!function_exists('log_comment')) {
	function log_comment(string $comment): void
	{
		echo "[log]$comment<br>\n";
	}
}
