<?php

namespace App\Engine;

use Illuminate\Support\Facades\Cache;

class Locale
{
	public static function getAvailableLanguages(): array
	{
		return Cache::remember('app-languages', 86400, fn() => array_values(array_diff(scandir(resource_path('lang')), ['..', '.'])));
	}

	public static function getUserPreferredLocale(): ?string
	{
		return auth()->user()?->locale;
	}

	public static function getPreferredLocale(): string
	{
		$locale = session()->get('locale') ??
			request()->get('locale') ??
			request()->cookie('app_locale') ??
			self::getUserPreferredLocale() ??
			config('app.locale', 'en') ??
			request()->getPreferredLanguage();

		return in_array($locale, self::getAvailableLanguages(), true) ? $locale : config('app.locale');
	}
}
