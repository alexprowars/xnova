<?php

class SyntaxTest extends PHPUnit_Framework_TestCase
{
	public function testCompile()
	{
		if (!defined('ROOT_PATH'))
		    define('ROOT_PATH', dirname(dirname(__FILE__)));

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT_PATH.'/app/', RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file)
		{
			if ($file->isFile())
			{
				if (strpos($file->getPathName(), '.php') === false && strpos($file->getPathName(), '.volt.cache') === false)
					continue;

				$r = shell_exec('php -l '.$file->getPathName());

				if (strpos($r, 'No syntax errors detected') === false)
					throw new \Exception($r);
			}
		}
	}
}