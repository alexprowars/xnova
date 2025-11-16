<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

class FileNamer extends DefaultFileNamer
{
	public function originalFileName(string $fileName): string
	{
		return md5($fileName . uniqid('', true) . microtime());
	}
}
