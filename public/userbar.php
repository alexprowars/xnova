<?php

use Friday\Core\Options;
use Phalcon\Loader;
use Phalcon\Events\Manager as EventsManager;
use Xnova\User;

define('ROOT_PATH', dirname(__DIR__.'../').'/');

require_once(ROOT_PATH."/app/modules/Core/Classes/Initializations.php");
require_once(ROOT_PATH."/app/modules/Core/Classes/Application.php");

$application = new Friday\Core\Application();

$di = $application->getDI();
$config = $di->getShared('config');

$loader = new Loader();

$loader->registerNamespaces([
	'Xnova' => ROOT_PATH.$config->application->baseDir.'modules/Xnova/Classes/',
	'Xnova\Models' => ROOT_PATH.$config->application->baseDir.'modules/Xnova/Models/',
	'Friday\Core' => ROOT_PATH.$config->application->baseDir.'modules/Core/Classes/',
	'Friday\Core\Models' => ROOT_PATH.$config->application->baseDir.'modules/Core/Models/',
], true);

$loader->register();

$di->set('loader', $loader);

$eventsManager = new EventsManager();

$application->initDatabase($di, $eventsManager);
$application->initCache($di);

/**
 * @var \Xnova\Database $db
 */
$db = $di->getShared('db');

error_reporting(E_ALL);

header ("Content-type: image/jpeg");

$id = intval($_SERVER['QUERY_STRING']);

if ($id > 0)
{
	$path = ROOT_PATH.$config->application->baseDir.$config->application->cacheDir.'/userbars';

	if (!file_exists($path))
		mkdir($path);

	if (false && file_exists($path.'/userbar_'.$id.'.jpg'))
	{
		echo file_get_contents($path.'/userbar_'.$id.'.jpg');

		$changeTime = filectime($path.'/userbar_'.$id.'.jpg');

		if ($changeTime < time() - 3600)
			unlink($path.'/userbar_'.$id.'.jpg');
	}
	else
	{
		$image = imagecreatefrompng(ROOT_PATH .'/public/assets/images/userbar.png');

		$lang = array();
		$lang[1]  = "Конфедерация";
		$lang[2]  = "Бионики";
		$lang[3]  = "Сайлоны";
		$lang[4]  = "Древние";

		/**
		 * @var $user \Xnova\Models\User
		 */
		$user = Xnova\Models\User::findFirst($id);

		if ($user)
		{
			$planet = $db->query("SELECT name, galaxy, system, planet FROM game_planets WHERE id_owner = ".$user->id." AND planet_type = 1 ORDER BY id LIMIT 1")->fetch();

			$stats = $db->query("SELECT `total_points`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $user->id . "';")->fetch();

			$color = "FFFFFF";
			$red = hexdec(substr($color,0,2));
			$green = hexdec(substr($color,2,4));
			$blue = hexdec(substr($color,4,6));
			$select = imagecolorallocate($image,$red,$green,$blue);
			$txt_shadow = imagecolorallocatealpha($image, 255, 255, 255, 255);
			$txt_color = imagecolorallocatealpha($image, 255, 255, 255, 2);
			$txt_shadow2 = imagecolorallocatealpha($image, 255, 255, 255, 255);
			$txt_color2 = imagecolorallocatealpha($image, 255, 255, 255, 40);

			// Имя пользователя
			imagettftext($image, 9, 0, 15, 25, $txt_shadow, ROOT_PATH."/public/assets/images/terminator.ttf", $user->username);
			imagettftext($image, 9, 0, 13, 23, $txt_color, ROOT_PATH."/public/assets/images/terminator.ttf", $user->username);

			// Вселенная
			imagettftext($image, 6, 0, 331, 76, $txt_shadow, ROOT_PATH."/public/assets/images/terminator.ttf", "XNOVA.SU UNI ".$config->game->universe);
			imagettftext($image, 6, 0, 330, 75, $txt_color, ROOT_PATH."/public/assets/images/terminator.ttf", "XNOVA.SU UNI ".$config->game->universe);

			// Планета
			imagettftext($image, 6, 0, 13, 37, $txt_color2, ROOT_PATH."/public/assets/images/KLMNFP2005.ttf", $planet['name']." [".$planet['galaxy'].":".$planet['system'].":".$planet['planet']."]");

			// Очки
			imagettftext($image, 6, 0, 13, 55, $txt_color, ROOT_PATH."/public/assets/images/KLMNFP2005.ttf", "Очки: ".\Xnova\Helpers::formatNumber(intval($stats['total_points']))."");
			imagettftext($image, 6, 0, 13, 70, $txt_color, ROOT_PATH."/public/assets/images/KLMNFP2005.ttf", "Место: ".\Xnova\Helpers::formatNumber(intval($stats['total_rank']))." из ".\Xnova\Helpers::formatNumber(Options::get('users_total', 0))."");

			// Дата генерации
			imagettftext($image, 6, 0, 365, 13, $txt_color, ROOT_PATH."/public/assets/images/KLMNFP2005.ttf", date("d.m.Y"));
			imagettftext($image, 6, 0, 377, 25, $txt_color, ROOT_PATH."/public/assets/images/KLMNFP2005.ttf", date("H:i:s"));

			$m = User::getRankId($user->lvl_minier);
			$f = User::getRankId($user->lvl_raid);

			$img = imagecreatetruecolor(32, 32);
			$source = imagecreatefrompng(ROOT_PATH.'/public/assets/images/ranks/m'.$m.'.png');
			imagealphablending($img, false);
			imagesavealpha($img, true);
			imagecopyresized($img, $source, 0, 0, 0, 0, 32, 32, 64, 64);

			imagecopy($image, $img, 250, 25, 0, 0, 32, 32);
			imagedestroy($img);
			imagedestroy($source);

			$img = imagecreatetruecolor(32, 32);
			$source = imagecreatefrompng(ROOT_PATH.'/public/assets/images/ranks/f'.$f.'.png');
			imagealphablending($img, false);
			imagesavealpha($img, true);
			imagecopyresized($img, $source, 0, 0, 0, 0, 32, 32, 64, 64);

			imagecopy($image, $img, 280, 25, 0, 0, 32, 32);
			imagedestroy($img);
			imagedestroy($source);

			// Расса
			imagettftext($image, 6, 0, 245, 65, $txt_color, ROOT_PATH."/public/assets/images/KLMNFP2005.ttf", $lang[$user->race]);
		}

		imagejpeg($image, $path.'/userbar_'.$id.'.jpg', 90);
		imagejpeg($image, NULL, 90);
		imagedestroy($image);
	}
}
else
{
	$image = imagecreatefrompng(ROOT_PATH.'/public/assets/images/userbar.png');
	imagejpeg($image, NULL, 85);
	imagedestroy($image);
}