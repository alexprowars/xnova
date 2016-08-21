<?php

namespace Friday\Core\Assets\Filters;

use Friday\Core\Assets\FilterInterface;
use Phalcon\Tag;

class CssImports implements FilterInterface
{
	/**
	 * @param $content string
	 * @param $resource \Phalcon\Assets\Resource
	 * @return mixed string
	 */
	public function filter($content, $resource)
	{
		$basePath = Tag::getUrlService()->getStaticBaseUri();

		$path = $basePath.dirname($resource->getPath());

		$content = preg_replace_callback(
			'#([;\s:]*(?:url|@import)\s*\(\s*)(\'|"|)(.+?)(\2)\s*\)#si',
			create_function('$matches', 'return $matches[1].Friday\Core\Assets\Manager::replaceUrlCSS($matches[3], $matches[2], "'.addslashes($path).'").")";'),
			$content
		);

		$content = preg_replace_callback(
			'#(\s*@import\s*)([\'"])([^\'"]+)(\2)#si',
			create_function('$matches', 'return $matches[1].Friday\Core\Assets\Manager::replaceUrlCSS($matches[3], $matches[2],"'.addslashes($path).'");'),
			$content
		);
		
		return $content;
	}
}