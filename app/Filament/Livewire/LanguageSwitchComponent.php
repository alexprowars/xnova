<?php

namespace App\Filament\Livewire;

use App\Filament\Plugins\LanguageSwitcherPlugin;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LanguageSwitchComponent extends Component
{
	public function changeLocale(string $locale)
	{
		/** @var LanguageSwitcherPlugin $plugin */
		$plugin = filament('language-switcher');

		abort_unless(auth()->check() && in_array($locale, array_column($plugin->getLocales(), 'code'), true), 403);

		auth()->user()->update(['locale' => $locale]);

		return redirect()->back();
	}

	public function render(): View
	{
		return view('filament.plugins.language.language-switch');
	}
}
