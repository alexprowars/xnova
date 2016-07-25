<?php

use Friday\Core\Options;
use Xnova\Database;
use Phalcon\Di\FactoryDefault\Cli as CliDI;

define('APP_PATH', dirname(__DIR__.'../').'/');

$di = new CliDI();

if (is_readable(APP_PATH . '/app/config/config.ini'))
{
	$config = new \Phalcon\Config\Adapter\Ini(APP_PATH . '/app/config/config.ini');

    $di->set('config', $config);

	include (APP_PATH . '/app/config/loader.php');

	$di->set(
	    'db', function () use ($config)
		{
			/**
			 * @var Object $config
			 */
			$connection = new Database([
	            'host' 		=> $config->database->host,
	            'username' 	=> $config->database->username,
	            'password' 	=> $config->database->password,
	            'dbname' 	=> $config->database->dbname,
				'options' 	=> [PDO::ATTR_PERSISTENT => false, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
	        ]);

			return $connection;
	    }
	);
}
else
	die('config.ini not found');

/**
 * @var \Xnova\Database $db
 */
$db = $di->getShared('db');

include (APP_PATH . '/app/config/bootstrap.php');

error_reporting(E_ALL);

header ("Content-type: image/jpeg");

$id = intval($_SERVER['QUERY_STRING']);

if ($id > 0)
{
	if (!file_exists(APP_PATH . $config->application->cacheDir.'/userbars'))
		mkdir(APP_PATH . $config->application->cacheDir.'/userbars');

	if (false && file_exists(APP_PATH . $config->application->cacheDir.'/userbars/userbar_'.$id.'.jpg'))
	{
		echo file_get_contents(APP_PATH . $config->application->cacheDir.'/userbars/userbar_'.$id.'.jpg');

		$changeTime = filectime(APP_PATH . $config->application->cacheDir.'/userbars/userbar_'.$id.'.jpg');

		if ($changeTime < time() - 3600)
			unlink(APP_PATH . $config->application->cacheDir.'/userbars/userbar_'.$id.'.jpg');
	}
	else
	{
		$image = imagecreatefrompng(APP_PATH .'/public/assets/images/userbar.png');

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
			imagettftext($image, 9, 0, 15, 25, $txt_shadow, APP_PATH."/public/assets/images/terminator.ttf", $user->username);
			imagettftext($image, 9, 0, 13, 23, $txt_color, APP_PATH."/public/assets/images/terminator.ttf", $user->username);

			// Вселенная
			imagettftext($image, 6, 0, 331, 76, $txt_shadow, APP_PATH."/public/assets/images/terminator.ttf", "XNOVA.SU UNI ".$config->game->universe);
			imagettftext($image, 6, 0, 330, 75, $txt_color, APP_PATH."/public/assets/images/terminator.ttf", "XNOVA.SU UNI ".$config->game->universe);

			// Планета
			imagettftext($image, 6, 0, 13, 37, $txt_color2, APP_PATH."/public/assets/images/KLMNFP2005.ttf", $planet['name']." [".$planet['galaxy'].":".$planet['system'].":".$planet['planet']."]");

			// Очки
			imagettftext($image, 6, 0, 13, 55, $txt_color, APP_PATH."/public/assets/images/KLMNFP2005.ttf", "Очки: ".\Xnova\Helpers::pretty_number(intval($stats['total_points']))."");
			imagettftext($image, 6, 0, 13, 70, $txt_color, APP_PATH."/public/assets/images/KLMNFP2005.ttf", "Место: ".\Xnova\Helpers::pretty_number(intval($stats['total_rank']))." из ".\Xnova\Helpers::pretty_number(Options::get('users_total', 0))."");

			// Дата генерации
			imagettftext($image, 6, 0, 365, 13, $txt_color, APP_PATH."/public/assets/images/KLMNFP2005.ttf", date("d.m.Y"));
			imagettftext($image, 6, 0, 377, 25, $txt_color, APP_PATH."/public/assets/images/KLMNFP2005.ttf", date("H:i:s"));

			$m = $user->getRankId($user->lvl_minier);
			$f = $user->getRankId($user->lvl_raid);

			$img = imagecreatetruecolor(32, 32);
			$source = imagecreatefrompng(APP_PATH.'/public/assets/images/ranks/m'.$m.'.png');
			imagealphablending($img, false);
			imagesavealpha($img, true);
			imagecopyresized($img, $source, 0, 0, 0, 0, 32, 32, 64, 64);

			imagecopy($image, $img, 250, 25, 0, 0, 32, 32);
			imagedestroy($img);
			imagedestroy($source);

			$img = imagecreatetruecolor(32, 32);
			$source = imagecreatefrompng(APP_PATH.'/public/assets/images/ranks/f'.$f.'.png');
			imagealphablending($img, false);
			imagesavealpha($img, true);
			imagecopyresized($img, $source, 0, 0, 0, 0, 32, 32, 64, 64);

			imagecopy($image, $img, 280, 25, 0, 0, 32, 32);
			imagedestroy($img);
			imagedestroy($source);

			// Расса
			imagettftext($image, 6, 0, 245, 65, $txt_color, APP_PATH."/public/assets/images/KLMNFP2005.ttf", $lang[$user->race]);
		}

		imagejpeg($image, APP_PATH . $config->application->cacheDir.'/userbars/userbar_'.$id.'.jpg', 90);
		imagejpeg($image, NULL, 90);
		imagedestroy($image);
	}
}
else
{
	$image = imagecreatefrompng(APP_PATH.'/public/assets/images/userbar.png');
	imagejpeg($image, NULL, 85);
	imagedestroy($image);
}