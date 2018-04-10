<?php

namespace Friday\Core\Helpers;

use Phalcon\Di;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Cache
{
	private static $cacheDirs = [
		'/public/assets/cache/',
		'/app/cache/views/'
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
			if (!file_exists(ROOT_PATH.$dir))
				continue;

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
			/** @var $cache \Phalcon\Cache\Backend */
			$cache = $di->getShared('cache');
			$cache->flush();
		}

		if ($di->has('modelsMetadata'))
		{
			/** @var $cache \Phalcon\Mvc\Model\MetaData */
			$cache = $di->getShared('modelsMetadata');
			$cache->reset();
		}
	}
}