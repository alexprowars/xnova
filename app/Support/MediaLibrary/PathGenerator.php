<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class PathGenerator extends DefaultPathGenerator
{
	public function getPath(Media $media): string
	{
		if ($media->hasCustomProperty('path')) {
			return $media->getCustomProperty('path') . '/';
		}

		return $this->getBasePath($media) . '/';
	}

	public function getPathForConversions(Media $media): string
	{
		return $this->getBasePath($media) . '/' . $media->getKey() . '_';
	}

	protected function getBasePath(Media $media): string
	{
		$prefix = config('media-library.prefix', '');

		$hash = md5($media->getKey());

		$path = sprintf(
			'%s/%s',
			substr($hash, 0, 2),
			substr($hash, 2, 2),
		);

		if ($prefix !== '') {
			return $prefix . '/' . $path;
		}

		return '/' . $path;
	}
}
