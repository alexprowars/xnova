<?php

use Phalcon\Mvc\View\Engine\Volt;

class ViewsTest extends PHPUnit_Framework_TestCase
{
	public function setUp ()
	{
		if (!defined('ROOT_PATH'))
		    define('ROOT_PATH', dirname(dirname(__FILE__)));

		if (!extension_loaded('phalcon'))
			dl('phalcon.so');

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT_PATH.'/app/cache/views/', RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file)
		{
			$todo = ($file->isDir() ? 'rmdir' : 'unlink');
			$todo($file->getRealPath());
		}
	}

	public function tearDown ()
	{
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT_PATH.'/app/cache/views/', RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file)
		{
			$todo = ($file->isDir() ? 'rmdir' : 'unlink');
			$todo($file->getRealPath());
		}
	}

	public function testCompile()
	{
		require_once(ROOT_PATH."/app/modules/Core/Classes/Initializations.php");
		require_once(ROOT_PATH."/app/modules/Core/Classes/Application.php");

		$application = new Friday\Core\Application();
		$application->run();

		$viewsDir = [
			"/app/modules/Xnova/Views",
			"/app/modules/Admin/Views",
		];

		$view = new \Phalcon\Mvc\View\Simple();

		$volt = new Volt($view, $application->getDI());

		$volt->setOptions([
			'compiledPath'		=> ROOT_PATH.'/app/cache/views/',
			'compiledSeparator'	=> '|',
			'compiledExtension'	=> '.cache',
			'compileAlways'		=> true
		]);

		$eventManager = $application->getDI()->getShared('eventsManager');

		$eventManager->fire('view:afterEngineRegister', $volt);

		$compiler = $volt->getCompiler();

		foreach ($viewsDir as $dir)
		{
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT_PATH.$dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($files as $file)
			{
				if ($file->isFile())
				{
					$compiler->compile($file->getPathname());
				}
			}
		}
	}
}