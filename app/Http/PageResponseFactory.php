<?php

namespace App\Http;

use Illuminate\Contracts\Support\Arrayable;
use Inertia\ProvidesInertiaProperties;
use Inertia\Response;
use Inertia\ResponseFactory;

class PageResponseFactory extends ResponseFactory
{
	/**
	 * @param  \BackedEnum|\UnitEnum|string  $component
	 * @param  array<array-key, mixed>|Arrayable<array-key, mixed>|ProvidesInertiaProperties  $props
	 */
	public function render($component, $props = []): Response
	{
		if ($props instanceof Arrayable) {
			$props = $props->toArray();
		} elseif ($props instanceof ProvidesInertiaProperties) {
			$props = [$props];
		}

		return parent::render($component, [
			'page' => $props,
		]);
	}
}
