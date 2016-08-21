<?php

namespace Friday\Core\Assets;

use Phalcon\Assets\Manager;

class Phalcon extends Manager
{
	public function output(Collection $collection, $callback, $type = null)
	{
		if (is_callable($callback) === false)
			throw new \Exception('Invalid parameter callback.');

		if (is_string($type) === false && is_null($type) === false)
			throw new \Exception('Invalid parameter type.');

		$output = "";

		$sourceBasePath = "";
		$targetBasePath = "";
		$filteredJoinedContent = "";

		$filters = $collection->getFilters();
		$prefix = $collection->getPrefix();

		$join = $collection->getJoin();

		if ($join)
		{
			$options = $this->_options;

			if (is_array($options))
			{
				$sourceBasePath = $options["sourceBasePath"];
				$targetBasePath = $options["targetBasePath"];
			}

			$collectionSourcePath = $collection->getSourcePath();

			if ($collectionSourcePath)
				$sourceBasePath .= $collectionSourcePath;

			$collectionTargetPath = $collection->getTargetPath();

			if ($collectionTargetPath)
				$targetBasePath .= $collectionTargetPath;

			if (!$targetBasePath)
				throw new \Exception("Path '". $targetBasePath. "' is not a valid target path (1)");

			if (is_dir($targetBasePath))
				throw new \Exception("Path '". $targetBasePath. "' is not a valid target path (2), is dir.");
		}

		foreach ($collection->getResources() as $resource)
		{
			/**
			 * @var $resource \Phalcon\Assets\Resource
			 */
			$filterNeeded = false;

			$local = $resource->getLocal();

			if ($join && $local)
			{
				$sourcePath = $resource->getRealSourcePath($sourceBasePath);

				if (!$sourcePath)
				{
					$sourcePath = $resource->getPath();

					throw new \Exception("Resource '". $sourcePath. "' does not have a valid source path");
				}

				$targetPath = $resource->getRealTargetPath($targetBasePath);

				if (!$targetPath)
					throw new \Exception("Resource '". $sourcePath. "' does not have a valid target path");

				if ($targetPath == $sourcePath)
					throw new \Exception("Resource '". $targetPath. "' have the same source and target paths");

				if (file_exists($targetPath))
				{
					if (filemtime($targetPath) >= filemtime($sourcePath))
						$filterNeeded = true;
				}
				else
					$filterNeeded = true;
			}
			else
			{
				$path = $resource->getRealTargetUri();

				if ($prefix)
					$prefixedPath = $prefix . $path;
				else
					$prefixedPath = $path;

				$attributes = $resource->getAttributes();

				$parameters = [];

				if (is_array($attributes))
				{
					$attributes[0] = $prefixedPath;
					$parameters[] = $attributes;
				}
				else
					$parameters[] = $prefixedPath;

				$parameters[] = $local;

				$html = call_user_func_array($callback, $parameters);

				if ($this->_implicitOutput)
					echo $html;
				else
					$output .= $html;

				continue;
			}

			if ($filterNeeded)
			{
				$content = $resource->getContent($sourceBasePath);

				if ($resource->getFilter() && !empty($filters))
				{
					foreach ($filters as $filter)
					{
						/**
						 * @var $filter \Friday\Core\Assets\FilterInterface
						 */

						if (!is_object($filter))
							throw new \Exception("Filter is invalid");

						$content = $filter->filter($content, $resource);
					}

					if ($join)
					{
						$filteredJoinedContent .= "\n/* Start: ".$resource->getPath()." */\n\n";

						if ($resource->getType() == "css")
							$filteredJoinedContent .= $content;
						else
							$filteredJoinedContent .= $content . ";";

						$filteredJoinedContent .= "\n\n/* End: ".$resource->getPath()." */\n";
					}
				}
				elseif ($join)
				{
					$filteredJoinedContent .= "\n/* Start: ".$resource->getPath()." */\n\n";
					$filteredJoinedContent .= $content;
					$filteredJoinedContent .= "\n\n/* End: ".$resource->getPath()." */\n";
				}

				if (!$join)
					file_put_contents($targetPath, $content);
			}

			if (!$join)
			{
				$path = $resource->getRealTargetUri();

				if ($prefix)
					$prefixedPath = $prefix . $path;
				else
					$prefixedPath = $path;

				$attributes = $resource->getAttributes();

				$local = true;

				$parameters = [];

				if (is_array($attributes))
				{
					$attributes[0] = $prefixedPath;
					$parameters[] = $attributes;
				}
				else
					$parameters[] = $prefixedPath;

				$parameters[] = $local;

				$html = call_user_func_array($callback, $parameters);

				if ($this->_implicitOutput)
					echo $html;
				else
					$output .= $html;
			}
		}

		if ($join)
		{
			file_put_contents($targetBasePath, $filteredJoinedContent);

			$targetUri = $collection->getTargetUri();

			if ($prefix)
				$prefixedPath = $prefix . $targetUri;
			else
				$prefixedPath = $targetUri;

			$attributes = $collection->getAttributes();

			$local = $collection->getTargetLocal();

			$parameters = [];

			if (is_array($attributes))
			{
				$attributes[0] = $prefixedPath;
				$parameters[] = $attributes;
			}
			else
				$parameters[] = $prefixedPath;

			$parameters[] = $local;

			$html = call_user_func_array($callback, $parameters);

			if ($this->_implicitOutput)
				echo $html;
			else
				$output .= $html;
		}

		return $output;
	}

	public function collection ($name)
	{
		if (!isset($this->_collections[$name]))
			$this->_collections[$name] = new Collection();

		return $this->_collections[$name];
	}
}