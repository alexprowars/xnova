<?php

use App\Support\ToastType;
use Inertia\Inertia;

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

if (!function_exists('toast')) {
	function toast(ToastType $type, string $message, ?string $title = null): void
	{
		$toasts = Inertia::getFlashed()['notifications'] ?? [];
		$toasts[] = [
			'id' => Str::uuid(),
			'type' => $type->value,
			'body' => $message,
			'title' => $title,
		];

		Inertia::flash('notifications', $toasts);
	}
}
