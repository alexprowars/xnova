<?php

namespace App\Engine\Traits\User;

/**
 * @property array $options
 */
trait HasOptions
{
	/**
	 * @var array<string, mixed>
	 */
	protected array $optionsDefault = [
		'bb_parser' 		=> true,
		'planetlist' 		=> false,
		'planetlistselect' 	=> false,
		'chatbox' 			=> true,
		'records' 			=> true,
		'only_available' 	=> false,
		'planet_sort'		=> 0,
		'planet_sort_order'	=> 0,
		'color'				=> 0,
		'timezone'			=> null,
		'spy'				=> 1,
	];

	/**
	 * @return array<string, mixed>
	 */
	public function getOptions(): array
	{
		return array_merge($this->optionsDefault, $this->options ?? []);
	}

	public function getOption(string $key): mixed
	{
		return ($this->options[$key] ?? ($this->optionsDefault[$key] ?? 0));
	}

	public function setOption(string $key, mixed $value): void
	{
		$options = $this->options ?? [];
		$options[$key] = $value;

		$this->options = $options;
	}
}
