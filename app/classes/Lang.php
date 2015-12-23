<?php
namespace App;

use Phalcon\Text;

class Lang
{
	static private $currentLang = '';
	static private $langArray 	= array();

	static function setLang ($lang_code)
	{
		self::$currentLang = $lang_code;

		if (is_file(APP_PATH.'app/messages/'.$lang_code.'/main.php'))
		{
			$lang = array();
			include(APP_PATH.'app/messages/'.$lang_code.'/main.php');

			self::$langArray = array_merge(self::$langArray, $lang);
			unset($lang);
		}
		else
			throw new \Exception('lang file not found!');
	}

	static function getLang ()
	{
		return self::$currentLang;
	}

	static function includeLang ($module)
	{
		if (!self::$currentLang)
			throw new \Exception('empty lang');

		if (is_file(APP_PATH.'app/messages/'.self::$currentLang.'/'.$module.'.php'))
		{
			$lang = array();
			include(APP_PATH.'app/messages/'.self::$currentLang.'/'.$module.'.php');

			self::$langArray = array_merge(self::$langArray, $lang);
			unset($lang);
		}
		else
			throw new \Exception('lang file not found!');
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
		else
			$value = false;

		if (count($args) > 1)
		{
			foreach ($args as $i => $arg)
			{
				if ($i > 0 && $value != false)
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
 
?>