<?php

namespace Friday\Core\Assets;

use Phalcon\Assets\Collection as PhalconCollection;

class Collection extends PhalconCollection
{
	public function addFilter($filter)
	{
		$this->_filters[] = $filter;
		
		return $this;
	}
}