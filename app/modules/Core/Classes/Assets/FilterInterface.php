<?php

namespace Friday\Core\Assets;

interface FilterInterface
{
	public function filter($content, $resource);
}