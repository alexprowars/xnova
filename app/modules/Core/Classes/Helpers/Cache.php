<?php

namespace Friday\Core\Helpers;

use Friday\Core\Admin\Controller;
use Friday\Core\Options;
use Phalcon\Di;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Cache
{
	private static $cacheDirs = [
		'/public/assets/cache/',
		'/app/cache/'
	];

	static function clearAll ()
	{
		self::clearFilesCache();
		self::clearApplicationCache();
	}

	static function clearFilesCache ()
	{
		foreach (self::$cacheDirs as $dir)
		{
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT_PATH.$dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($files as $file)
			{
				$todo = ($file->isDir() ? 'rmdir' : 'unlink');
				$todo($file->getRealPath());
			}
		}
	}

	static function clearApplicationCache ()
	{
		if (function_exists('apc_clear_cache'))
			apc_clear_cache();

		$di = Di::getDefault();

		if (!$di->has('cache') && $di->has('app'))
		{
			$application = $di->getShared('app');

			$application->initCache($di, $di->getShared('config'));
		}

		if ($di->has('cache'))
		{
			/**
			 * @var $cache \Phalcon\Cache\BackendInterface
			 */
			$cache = $di->getShared('cache');

			$cache->flush();

			/*$cache->delete(Options::CACHE_KEY);
			$cache->delete(Controller::CACHE_KEY_MENU);

			if ($di->has('registry'))
			{
				$registry = $di->get('registry');

				if (!empty($registry->modules))
				{
					foreach ($registry->modules as $module)
					{
						$cache->delete('ROUTER_RESOURCES_'.$module['code']);
					}
				}
			}*/
		}
	}
}