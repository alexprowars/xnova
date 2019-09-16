<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Xnova\Controller;
use Xnova\Models;

class CreditsController extends Controller
{
	public function index ()
	{
		$parse = [];

		$userId = Request::post('userId') && (int) Request::post('userId') > 0 ?
			(int) Request::post('userId') : $this->user->getId();

		$parse['id'] = $userId;
		$parse['payment'] = false;

		if (Request::post('summ'))
		{
			$summ = (int) Request::post('summ', 0);

			do {
				$id = mt_rand(1000000000000, 9999999999999);
			}
			while (DB::selectOne("SELECT id FROM users_payments WHERE transaction_id = ".$id) ? true : false);

			/** @var Models\UsersInfo $info */
			$info = Models\UsersInfo::query()->find($this->user->getId());

			$parse['payment'] = [
				'id' => $id,
				'hash' => md5(Config::get('settings.robokassa.login').":".$summ.":".$id.":".Config::get('settings.robokassa.public').":Shp_UID=".$parse['id']),
				'summ' => $summ,
				'email' => $info->email,
				'merchant' => Config::get('settings.robokassa.login'),
			];
		}

		$this->setTitle('Покупка кредитов');
		$this->showTopPanel(false);

		return $parse;
	}
}