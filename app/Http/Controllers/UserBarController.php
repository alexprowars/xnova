<?php

namespace App\Http\Controllers;

use App\Format;
use App\Models\Planet;
use App\Models\User;
use App\Settings;

class UserBarController extends Controller
{
	public function index(int $id)
	{
		$user = User::find($id);

		response(null)
			->header('Content-Type', 'image/jpeg')
			->sendHeaders();

		if (!$user) {
			$image = imagecreatefrompng(public_path('/assets/images/userbar.png'));
			imagejpeg($image, null, 85);

			return;
		}

		$settings = app(Settings::class);

		$planet = Planet::findOne($user->planet_id);
		$stats = $user->statistics;

		$image = imagecreatefrompng(public_path('/assets/images/userbar.png'));

		$txt_shadow = imagecolorallocatealpha($image, 255, 255, 255, 100);
		$txt_color = imagecolorallocatealpha($image, 255, 255, 255, 2);
		$txt_color2 = imagecolorallocatealpha($image, 255, 255, 255, 40);

		// Имя пользователя
		imagettftext($image, 9, 0, 15, 25, $txt_shadow, public_path('/assets/images/terminator.ttf'), $user->username);
		imagettftext($image, 9, 0, 13, 23, $txt_color, public_path('/assets/images/terminator.ttf'), $user->username);

		// Вселенная
		imagettftext($image, 7, 0, 381, 76, $txt_shadow, public_path('/assets/images/terminator.ttf'), config('app.name'));
		imagettftext($image, 7, 0, 380, 75, $txt_color, public_path('/assets/images/terminator.ttf'), config('app.name'));

		// Планета
		imagettftext($image, 6, 0, 13, 37, $txt_color2, public_path('/assets/images/KLMNFP2005.ttf'), $planet->name . ' ' . $planet->coordinates);

		// Очки
		imagettftext($image, 6, 0, 13, 55, $txt_color, public_path('/assets/images/KLMNFP2005.ttf'), 'Очки: ' . Format::number($stats->total_points ?? 0));
		imagettftext($image, 6, 0, 13, 70, $txt_color, public_path('/assets/images/KLMNFP2005.ttf'), 'Место: ' . Format::number($stats->total_rank ?? 0) . ' из ' . Format::number($settings->usersTotal));

		// Дата генерации
		imagettftext($image, 6, 0, 365, 13, $txt_color, public_path('/assets/images/KLMNFP2005.ttf'), date('d.m.Y'));
		imagettftext($image, 6, 0, 377, 25, $txt_color, public_path('/assets/images/KLMNFP2005.ttf'), date('H:i:s'));

		$m = User::getRankId($user->lvl_minier);
		$f = User::getRankId($user->lvl_raid);

		$img = imagecreatetruecolor(32, 32);
		$source = imagecreatefrompng(public_path('/assets/images/ranks/m' . $m . '.png'));
		imagealphablending($img, false);
		imagesavealpha($img, true);
		imagecopyresized($img, $source, 0, 0, 0, 0, 32, 32, 64, 64);

		imagecopy($image, $img, 250, 25, 0, 0, 32, 32);

		$img = imagecreatetruecolor(32, 32);
		$source = imagecreatefrompng(public_path('/assets/images/ranks/f' . $f . '.png'));
		imagealphablending($img, false);
		imagesavealpha($img, true);
		imagecopyresized($img, $source, 0, 0, 0, 0, 32, 32, 64, 64);

		imagecopy($image, $img, 280, 25, 0, 0, 32, 32);

		// Расса
		imagettftext($image, 6, 0, 245, 65, $txt_color, public_path('/assets/images/KLMNFP2005.ttf'), __('main.race.' . $user->race));

		imagejpeg($image, null, 90);
	}
}
