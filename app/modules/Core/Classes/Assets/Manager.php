<?php

namespace Friday\Core\Assets;

use Friday\Core\Assets\Filters\CssImports;
use Friday\Core\Assets\Filters\Cssmin;
use Friday\Core\Assets\Filters\Jsmin;
use Friday\Core\Options;
use Phalcon\Assets\Manager as AssetManager;
use Phalcon\Tag;

class Manager
{
	/**
	 * @var AssetManager
	 */
	private $_manager;

	const DEFAULT_COLLECTION_JS = 'js';
	const DEFAULT_COLLECTION_CSS = 'css';

	/** @var array of css files */
	private $css = [];

	/** @var array of js files */
	private $js = [];

	public function __construct ()
	{
		$this->_manager = new AssetManager();
	}

	public function addJs ($path, $options = false)
	{
		if (strlen($path) <= 0)
			return false;

		$options = $this->prepareAssetOptions($options);

		$path = $this->getAssetPath($path);

		$this->js[] = [
			'path' 		=> $path,
			'options' 	=> $options,
			'local'		=> !$this->isExternal($path)
		];

		return true;
	}

	public function addCss ($path, $options = false)
	{
		if (strlen($path) <= 0)
			return false;

		$options = $this->prepareAssetOptions($options);

		$path = $this->getAssetPath($path);

		$this->css[] = [
			'path' 		=> $path,
			'options' 	=> $options,
			'local'		=> !$this->isExternal($path)
		];

		return true;
	}

	public function isExternal($src)
	{
		return (strncmp($src, 'http://', 7) == 0 || strncmp($src, 'https://', 8) == 0 || strncmp($src, '//', 2) == 0);
	}

	public function getAssetPath($src)
	{
		if (($p = strpos($src, "?")) > 0 && !$this->isExternal($src))
			$src = substr($src, 0, $p);

		if (!$this->isExternal($src))
			$src = ltrim($src, '/');

		return $src;
	}

	public function outputCss ($target = self::DEFAULT_COLLECTION_CSS)
	{
		$collection = $this->_manager->collection($target);

		$resources = $this->sortAssets($this->css);

		$hashString = '';

		foreach ($resources as $resource)
		{
			$collection->addCss($resource['path'], $resource['local'], $resource['local']);

			$hashString .= $resource['path'];
		}

		$targetName = 'assets_'.md5($hashString);

		$collection->join((Options::get('assets_join_css', VALUE_FALSE) == VALUE_TRUE));

		$content = "";

		if ($collection->getJoin())
		{
			$collection->setTargetPath(ROOT_PATH.'/public/assets/cache/'.$targetName.'.css');
			$collection->setTargetUri('/assets/cache/'.$targetName.'.css');

			$filename = $collection->getTargetPath();

			if (file_exists($filename))
			{
				$time = 0;

				foreach ($collection->getResources() as $resource)
				{
					/**
					 * @var $resource \Phalcon\Assets\Resource
					 */
					if ($resource->getLocal())
					{
						$tmp = filemtime($resource->getRealTargetPath());

						if ($tmp > $time)
							$time = $tmp;
					}
					else
						$content .= Tag::stylesheetLink($resource->getRealTargetPath());
				}

				if (filemtime($filename) > $time)
				{
					$collection->setTargetUri($collection->getTargetUri() . '?' . filemtime($filename));

					return $content.Tag::stylesheetLink($collection->getTargetUri());
				}
			}

			$collection->addFilter(new CssImports());

			if (Options::get('assets_minify_css', VALUE_FALSE) == VALUE_TRUE)
				$collection->addFilter(new Cssmin());
		}

		return $this->_manager->output($collection, ["Phalcon\\Tag", "stylesheetLink"], "css");
	}

	public function outputJs ($target = self::DEFAULT_COLLECTION_JS)
	{
		$collection = $this->_manager->collection($target);

		$resources = $this->sortAssets($this->js);

		$hashString = '';

		foreach ($resources as $resource)
		{
			$collection->addJs($resource['path'], $resource['local'], $resource['local']);

			$hashString .= $resource['path'];
		}

		$targetName = 'assets_'.md5($hashString);

		$collection->join((Options::get('assets_join_js', VALUE_FALSE) == VALUE_TRUE));

		$content = "";

		if ($collection->getJoin())
		{
			$collection->setTargetPath(ROOT_PATH.'/public/assets/cache/'.$targetName.'.js');
			$collection->setTargetUri('/assets/cache/'.$targetName.'.js');
			
			$filename = $collection->getTargetPath();

			if (file_exists($filename))
			{
				$time = 0;

				foreach ($collection->getResources() as $resource)
				{
					/**
					 * @var $resource \Phalcon\Assets\Resource
					 */
					if ($resource->getLocal())
					{
						$tmp = filemtime($resource->getRealTargetPath());

						if ($tmp > $time)
							$time = $tmp;
					}
					else
						$content .= Tag::javascriptInclude($resource->getRealTargetPath());
				}

				if (filemtime($filename) > $time)
				{
					$collection->setTargetUri($collection->getTargetUri() . '?' . filemtime($filename));

					return $content.Tag::javascriptInclude($collection->getTargetUri());
				}
			}

			if (Options::get('assets_minify_js', VALUE_FALSE) == VALUE_TRUE)
				$collection->addFilter(new Jsmin());
		}

		return $this->_manager->output($collection, ["Phalcon\\Tag", "javascriptInclude"], "js");
	}

	private function sortAssets ($resources = [])
	{
		$sorted = [];
		$unsorted = [];

		foreach ($resources as $resource)
		{
			if ($resource['options']['sort'] <= 0)
				$unsorted[] = $resource;
			else
				$sorted[] = $resource;
		}

		uasort ($sorted, function ($a, $b)
		{
			return ($a['options']['sort'] > $b['options']['sort'] ? 1 : ($a['options']['sort'] == $b['options']['sort'] ? 0 : -1));
		});

		return array_merge($unsorted, $sorted);
	}

	private function prepareAssetOptions ($options)
	{
		if (is_numeric($options))
			$options = ['sort' => $options];
		elseif (is_string($options))
			$options = ['collection' => $options];
		elseif (!is_array($options))
			$options = [];

		if (!isset($options['sort']))
			$options['sort'] = 0;

		return $options;
	}

	public static function replaceUrlCss($url, $quote, $path)
	{
		if (strpos($url, "://") !== false || strpos($url, "data:") !== false)
			return $quote.$url.$quote;

		$url = trim(stripslashes($url), "'\" \r\n\t");

		if (substr($url, 0, 1) == "/")
			return $quote.$url.$quote;

		return $quote.$path.'/'.$url.$quote;
	}

	public function clearJs ()
	{
		$this->js = [];
	}

	public function clearCss ()
	{
		$this->css = [];
	}
}