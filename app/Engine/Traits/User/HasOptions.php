<?php

namespace App\Engine\Traits\User;

trait HasOptions
{
	protected $optionsDefault = [
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

	public function getOptions()
	{
		return array_merge($this->optionsDefault, $this->options ?? []);
	}

	public function getOption($key)
	{
		return ($this->options[$key] ?? ($this->optionsDefault[$key] ?? 0));
	}

	public function setOption($key, $value)
	{
		$options = $this->options ?? [];
		$options[$key] = $value;

		$this->options = $options;
	}
}
