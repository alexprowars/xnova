<?php

namespace Friday\Core;

use Phalcon\Text;

class Lang
{
	static private $currentLang = '';
	static private $langArray 	= [];

	static function setLang ($langCode, $moduleId = 'core')
	{
		self::$currentLang = $langCode;
		self::includeLang('main', $moduleId);
	}

	static function getLang ()
	{
		return self::$currentLang;
	}

	static function includeLang ($file = 'main', $moduleId = 'core')
	{
		if (!self::$currentLang)
			return;

		$moduleId = Text::lower($moduleId);

		if (!isset(self::$langArray[$moduleId]))
			self::$langArray[$moduleId] = [];

		if (file_exists(ROOT_PATH.'/app/modules/'.ucfirst($moduleId).'/Langs/'.self::$currentLang.'/'.$file.'.php'))
		{
			$lang = [];
			include(ROOT_PATH.'/app/modules/'.ucfirst($moduleId).'/Langs/'.self::$currentLang.'/'.$file.'.php');

			self::$langArray[$moduleId] = array_merge(self::$langArray[$moduleId], $lang);
			unset($lang);
		}
	}

	/**
	 * @return mixed
	 */
	static function getText ()
	{
		if (func_num_args() < 1)
			return '##EMPTY_PARAMS##';

		$args = func_get_args();

		if (func_num_args() == 1 && is_array($args[0]))
			$args = $args[0];

		if (isset(self::$langArray[$args[0]]))
			$value = self::$langArray[$args[0]];
		elseif (isset(self::$langArray['core'][$args[0]]))
			$value = self::$langArray['core'][$args[0]];
		else
			$value = false;

		if (count($args) > 1)
		{
			foreach ($args as $i => $arg)
			{
				if (is_bool($arg))
					return $value;

				if ($i > 0 && is_array($value))
				{
					if (isset($value[$arg]))
						$value = $value[$arg];
					else
						$value = false;
				}
			}
		}

		if ($value !== false)
			return $value;
		else
			return '##'. Text::upper(implode('::', $args)).'##';
	}
}