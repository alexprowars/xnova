<?php

namespace App\Filament\Plugins;

use App\Filament\Livewire\LanguageSwitchComponent;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

class LanguageSwitcherPlugin implements Plugin
{
	protected array $locales = [];

	public static function make(): static
	{
		return app(static::class);
	}

	public function getId(): string
	{
		return 'language-switcher';
	}

	public function register(Panel $panel): void
	{
		Livewire::component('filament-language-switcher', LanguageSwitchComponent::class);
	}

	public function boot(Panel $panel): void
	{
		FilamentView::registerRenderHook(
			PanelsRenderHook::GLOBAL_SEARCH_AFTER,
			fn () => auth()->check() ? Blade::render('<livewire:filament-language-switcher key="language-switcher" />') : ''
		);
	}

	public function locales(array $locales): static
	{
		$this->locales = $locales;

		return $this;
	}

	public function getLocales(): array
	{
		return $this->locales;
	}

	public function getCurrentLocale(): array
	{
		return collect($this->locales)->firstWhere('code', app()->getLocale()) ?? $this->locales[0];
	}
}
