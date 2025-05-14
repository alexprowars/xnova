<?php

namespace App\Models;

use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Throwable;

class Media extends BaseMedia
{
	public function getUrl(string $conversionName = ''): string
	{
		if (!empty($conversionName)) {
			if (!$this->hasGeneratedConversion($conversionName)) {
				try {
					app(FileManipulator::class)->createDerivedFiles($this, [$conversionName]);
				} catch (Throwable) {
				}
			}

			return $this->hasGeneratedConversion($conversionName) ?
				parent::getUrl($conversionName) :
				parent::getUrl();
		}

		return parent::getUrl($conversionName);
	}
}
