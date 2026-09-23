@php
	$plugin = filament('language-switcher');
	$locales = $plugin->getLocales();
	$currentLocale = $plugin->getCurrentLocale();
@endphp

<x-filament::dropdown
	:teleport="true"
	placement="bottom-end"
	width="w-fit fls-dropdown-width"
	max-height="max-content"
	class="fi-dropdown fi-user-menu"
	data-nosnippet="true"
>
	<x-slot name="trigger">
		<div
			class="flex items-center justify-center w-9 h-9 language-switch-trigger text-primary-600 bg-primary-500/10 rounded-full p-1 ring-2 ring-inset ring-gray-200 hover:ring-gray-300 dark:ring-gray-500 hover:dark:ring-gray-400"
			x-tooltip="{
				content: @js($currentLocale['name']),
				theme: $store.theme,
				placement: 'right'
			}"
		>
			<div class="w-7 h-7 overflow-hidden rounded-full">
				{{ svg('flag-1x1-' . $currentLocale['flag'], 'object-cover object-center') }}
			</div>
		</div>
	</x-slot>

	<x-filament::dropdown.list class="border-t-0 space-y-1 p-1.5">
		@foreach ($locales as $locale)
			@if (!app()->isLocale($locale['code']))
				<button
					type="button"
					wire:click="changeLocale('{{ $locale['code'] }}')"
					class="flex items-center w-full transition-colors duration-75 rounded-md outline-none fi-dropdown-list-item whitespace-nowrap disabled:pointer-events-none disabled:opacity-70 fi-dropdown-list-item-color-gray hover:bg-gray-950/5 focus:bg-gray-950/5 dark:hover:bg-white/5 dark:focus:bg-white/5 justify-start p-2"
				>
					<div class="w-7 h-7 overflow-hidden rounded-full">
						{{ svg('flag-1x1-' . $locale['flag'], 'object-cover object-center') }}
					</div>
					<span class="text-sm font-medium text-gray-600 hover:bg-transparent dark:text-gray-200">
						{{ $locale['name'] }}
					</span>
				</button>
			@endif
		@endforeach
	</x-filament::dropdown.list>
</x-filament::dropdown>
