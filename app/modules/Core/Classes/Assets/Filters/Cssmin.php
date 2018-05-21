<?php

namespace Friday\Core\Assets\Filters;

use Friday\Core\Assets\FilterInterface;

class Cssmin implements FilterInterface
{
	/**
	 * @param $content string
	 * @param $resource \Phalcon\Assets\Resource
	 * @return mixed string
	 */
	public function filter($content, $resource)
	{
		if (strpos($resource->getPath(), '.min') !== false)
			return $content;

		$filter = new \Phalcon\Assets\Filters\Cssmin();

		return $filter->filter($content);
	}
}