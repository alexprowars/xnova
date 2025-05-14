<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class MediaLibraryPathGenerator extends DefaultPathGenerator
{
	public function getPathForConversions(Media $media): string
	{
		return sprintf('%04d', (int) ($media->getKey() / 1000)) . '/' . $media->getKey() . '_';
	}

	protected function getBasePath(Media $media): string
	{
		if (isset($media->custom_properties['path'])) {
			return $media->custom_properties['path'];
		}

		return sprintf('%04d', (int) ($media->getKey() / 1000)) . '/' . $media->getKey();
	}
}
