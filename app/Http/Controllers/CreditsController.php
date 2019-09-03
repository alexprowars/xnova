<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Xnova\Controller;
use Xnova\Models\UserInfo;

class CreditsController extends Controller
{
	public function index ()
	{
		$parse = [];

		$userId = Input::post('userId') && (int) Input::post('userId') > 0 ?
			(int) Input::post('userId') : $this->user->getId();

		$parse['id'] = $userId;
		$parse['payment'] = false;

		if (Input::post('summ'))
		{
			$summ = (int) Input::post('summ', 0);

			do {
				$id = mt_rand(1000000000000, 9999999999999);
			}
			while (DB::selectOne("SELECT id FROM users_payments WHERE transaction_id = ".$id) ? true : false);

			$info = UserInfo::query()->find($this->user->getId());

			$parse['payment'] = [
				'id' => $id,
				'hash' => md5(Config::get('game.robokassa.login').":".$summ.":".$id.":".Config::get('game.robokassa.public').":Shp_UID=".$parse['id']),
				'summ' => $summ,
				'email' => $info->email,
				'merchant' => Config::get('game.robokassa.login'),
			];
		}

		$this->setTitle('Покупка кредитов');
		$this->showTopPanel(false);

		return $parse;
	}
}