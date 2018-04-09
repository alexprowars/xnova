<?php

namespace Friday\Core;

use Phalcon\Di;
use Phalcon\Http\Request\File;

class Files
{
	public static function save (File $file)
	{
		$fileName = $file->getName();

		$fileName = rtrim($fileName, "\0.\\/+ ");
		$fileName = str_replace("\\", "/", $fileName);
		$fileName = rtrim($fileName, "/");

		$p = mb_strrpos($fileName, "/");

		if ($p !== false)
			$fileName = mb_substr($fileName, $p + 1);

		if (preg_match('/\.(php|php5|php4|php3|phtml|pl|py|cgi|asp|js)$/i', $fileName))
			throw new \Exception('invalid name');

		$fileModel = new Models\File();
		$fileModel->name = $fileName;

		$fileName = Helpers::translite($fileName);

		if (strlen($fileName) > 255)
			throw new \Exception('Слишком длинное имя файла');

		$dir = ROOT_PATH.'/public/upload';
		$subdir = '';

		$i = 0;

		while (true)
		{
			$subdir = substr(md5(uniqid("", true)), 0, 3);

			if (!file_exists($dir."/".$subdir."/".$fileName))
				break;

			if ($i >= 25)
			{
				$j = 0;

				while(true)
				{
					$subdir = substr(md5(mt_rand()), 0, 3)."/".substr(md5(mt_rand()), 0, 3);

					if (!file_exists($dir."/".$subdir."/".$fileName))
						break;

					if ($j >= 25)
					{
						$subdir = substr(md5(mt_rand()), 0, 3)."/".md5(mt_rand());

						break;
					}

					$j++;
				}

				break;
			}

			$i++;
		}

		p($dir."/".$subdir);

		if (!is_dir($dir."/".$subdir))
			mkdir($dir."/".$subdir);

		$fileModel->mime = $file->getRealType();
		$fileModel->size = $file->getSize();

		if (!$file->moveTo($dir."/".$subdir."/".$fileName))
			throw new \Exception('Не удалось записать файл');

		$fileModel->src = $subdir.'/'.$fileName;

		if (!$fileModel->save())
		{
			unlink($dir."/".$subdir."/".$fileName);

			throw new \Exception('Не удалось сохранить файл');
		}

		return $fileModel->id;
	}

	public static function getById ($fileId)
	{
		$fileId = (int) $fileId;

		if (!$fileId)
			return false;

		$file = Models\File::findFirst((int) $fileId);

		if (!$file)
			return false;

		$src = Di::getDefault()->getShared('url')->getStatic('upload/'.$file->src);

		return [
			'id' => (int) $file->id,
			'size' => (int) $file->size,
			'name' => $file->name,
			'mime' => $file->mime,
			'src' => $src,
			'model' => $file,
		];
	}

	public static function delete ($fileId)
	{
		$file = self::getById($fileId);

		if (!$file)
			return true;

		if (unlink(ROOT_PATH.'/public/upload/'.$file['model']->src))
			$file['model']->delete();

		return true;
	}
}