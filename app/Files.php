<?php

namespace Xnova;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Xnova\Exceptions\Exception;

class Files
{
	public static function save(UploadedFile $file)
	{
		$fileName = $file->getClientOriginalName();

		$fileName = rtrim($fileName, "\0.\\/+ ");
		$fileName = str_replace("\\", "/", $fileName);
		$fileName = rtrim($fileName, "/");

		$p = mb_strrpos($fileName, "/");

		if ($p !== false) {
			$fileName = mb_substr($fileName, $p + 1);
		}

		if (preg_match('/\.(php|php5|php4|php3|phtml|pl|py|cgi|asp|js)$/i', $fileName)) {
			throw new Exception('invalid name');
		}

		$fileModel = new Models\File();
		$fileModel->name = $fileName;

		$fileInfo = pathinfo($fileName);
		$fileInfo['filename'] = Helpers::translite($fileInfo['filename']);

		if (strlen($fileInfo['filename']) > 255) {
			throw new Exception('Слишком длинное имя файла');
		}

		$fileName = $fileInfo['filename'] . '.' . $fileInfo['extension'];

		/** @var FilesystemAdapter $storage */
		$storage = Storage::disk('public');

		/** @noinspection PhpUndefinedMethodInspection */
		$dir = $storage->getDriver()->getAdapter()->getPathPrefix() . 'files';
		$subdir = '';

		$i = 0;

		while (true) {
			$subdir = substr(md5(uniqid("", true)), 0, 3);

			if (!file_exists($dir . "/" . $subdir . "/" . $fileName)) {
				break;
			}

			if ($i >= 25) {
				$j = 0;

				while (true) {
					$subdir = substr(md5(mt_rand()), 0, 3) . "/" . substr(md5(mt_rand()), 0, 3);

					if (!file_exists($dir . "/" . $subdir . "/" . $fileName)) {
						break;
					}

					if ($j >= 25) {
						$subdir = substr(md5(mt_rand()), 0, 3) . "/" . md5(mt_rand());

						break;
					}

					$j++;
				}

				break;
			}

			$i++;
		}

		$fileModel->mime = $file->getMimeType();
		$fileModel->size = $file->getSize();

		if ($storage->putFileAs('files/' . $subdir, $file, $fileName)) {
			throw new Exception('Не удалось записать файл');
		}

		$fileModel->src = $subdir . '/' . $fileName;

		if ($fileModel->save()) {
			return $fileModel->id;
		}

		$storage->delete('files/' . $subdir . '/' . $fileName);

		throw new Exception('Не удалось сохранить файл');
	}

	public static function getById(int $fileId)
	{
		static $_staticCache = [];

		$fileId = (int) $fileId;

		if (!$fileId) {
			return false;
		}

		if (isset($_staticCache[$fileId])) {
			return $_staticCache[$fileId];
		}

		$result = Cache::get('core::file_' . $fileId);

		if ($result !== null) {
			$_staticCache[$fileId] = $result;

			return $result;
		}

		/** @var Models\File $file */
		$file = Models\File::query()->find((int) $fileId);

		if (!$file) {
			return false;
		}

		/** @var FilesystemAdapter $storage */
		$storage = Storage::disk('public');

		$result = [
			'id' => (int) $file->id,
			'size' => (int) $file->size,
			'name' => $file->name,
			'mime' => $file->mime,
			'src' => $storage->url('files/' . $file->src),
			'path' => $storage->path('files/' . $file->src),
		];

		Cache::put('core::file_' . $fileId, $result, 600);
		$_staticCache[$fileId] = $result;

		return $result;
	}

	public static function delete(int $fileId)
	{
		/** @var Models\File $file */
		$file = Models\File::query()->find((int) $fileId);

		if (!$file) {
			return true;
		}

		if (Storage::disk('public')->delete('files/' . $file->src)) {
			$file->delete();
		}

		return true;
	}
}
