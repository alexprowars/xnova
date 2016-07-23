<?php

namespace Friday\Core\Assets\Filters;

use Friday\Core\Assets\FilterInterface;

class Jsmin implements FilterInterface
{
	/**
	 * @param $content string
	 * @param $resource \Phalcon\Assets\Resource
	 * @return mixed string
	 */
	public function filter($content, $resource)
	{
		if (strpos($resource->getPath(), '.min') === false)
		{
			$filter = new \Phalcon\Assets\Filters\Jsmin();

			return $filter->filter($content);
		}
		else
			return $content;
	}
}