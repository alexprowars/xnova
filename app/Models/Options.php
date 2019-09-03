<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string|null $value
 * @property string $group_id
 * @property string $type
 * @property string $def
 * @property string $description
 * @property string $cached
 */
class Options extends Model
{
	public $timestamps = false;
	public $table = 'options';
	protected $attributes = [
		'def' => '',
		'description' => '',
	];
	protected $fillable = [
		'name',
		'value',
	];

	const CACHE_KEY = 'APP_OPTIONS';
	const CACHE_TIME = 600;

	public static function getAll ()
	{
		$data = [];

		$options = self::all();

		foreach ($options as $option)
			$data[$option->name] = is_null($option->value) ? $option->def : $option->value;

		Cache::put(self::CACHE_KEY,$data, self::CACHE_TIME);

		return $data;
	}

	public static function exists ($key)
	{
		return self::query()->where('name', $key)->exists();
	}

	public static function get ($key, $default = null)
	{
		if ($option = self::query()->where('name', $key)->first())
			return $option->value;

		return $default;
	}

	public static function set ($key, $value = null)
	{
		$keys = is_array($key) ? $key : [$key => $value];

		foreach ($keys as $key => $value)
		{
			self::query()->updateOrCreate(['name' => $key], ['value' => $value]);
			Config::set('game.'.$key, $value);
		}

		Cache::forget(self::CACHE_KEY);
	}

	public static function remove ($key)
	{
		Config::set('game.'.$key, null);

		return (bool) self::query()->where('name', $key)->delete();
	}
}