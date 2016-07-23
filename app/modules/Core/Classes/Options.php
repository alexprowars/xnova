<?php

namespace Friday\Core;

use Friday\Core\Models\Option;
use Phalcon\Di;

class Options
{
	const CACHE_KEY = 'FRIDAY_OPTIONS';
	const CACHE_TIME = 3600;

	protected static $options = [];

	public static function get ($name, $default = "")
	{
		if (empty($name))
			throw new \Exception("ArgumentNullException");

		if (empty(self::$options))
			self::load();

		if (isset(self::$options[$name]))
			return self::$options[$name];

		return $default;
	}

	private static function load ()
	{
		/**
		 * @var $cache \Phalcon\Cache\BackendInterface
		 */
		$cache = Di::getDefault()->getShared('cache');

		$data = $cache->get(self::CACHE_KEY);

		if (!is_array($data))
		{
			$options = Option::find();

			foreach ($options as $option)
			{
				self::$options[$option->name] = is_null($option->value) ? $option->default : $option->value;
			}

			$cache->save(self::CACHE_KEY, self::$options, self::CACHE_TIME);
		}
		else
			self::$options = $data;
	}

	public static function set ($name, $value = "")
	{
		if (empty($name))
			throw new \Exception("ArgumentNullException");

		$option = Option::findFirst(["conditions" => "name = :name:", "bind" => ["name" => $name]]);

		if ($option)
		{
			$option->value = $value;
			$option->update();

			/**
			 * @var $cache \Phalcon\Cache\BackendInterface
			 */
			$cache = Di::getDefault()->getShared('cache');

			$cache->delete(self::CACHE_KEY);
		}
		else
			throw new \Exception("OptionNotFound");
	}

	public static function create ($data)
	{
		if (!isset($data['name']) || empty($data['name']))
			throw new \Exception("ArgumentNullException");

		$option = Option::findFirst(["conditions" => "name = :name:", "bind" => ["name" => $data['name']]]);

		if (!$option)
		{
			$option = new Option();

			if (!isset($data['value']))
				$data['value'] = '';

			$option->name 	= $data['name'];
			$option->value 	= $data['value'];

			if (isset($data['default']))
				$option->default = $data['value'];

			if (isset($data['description']))
				$option->description = $data['description'];

			if (isset($data['type']))
				$option->type = $data['type'];

			if (isset($data['group']))
				$option->group = $data['group'];

			$option->create();
		}
		else
			throw new \Exception("OptionIsExist");
	}

	public static function delete ($name)
	{
		if (empty($name))
			throw new \Exception("ArgumentNullException");

		$option = Option::findFirst(["conditions" => "name = :name:", "bind" => ["name" => $name]]);

		if ($option)
			return $option->delete();

		return false;
	}
}