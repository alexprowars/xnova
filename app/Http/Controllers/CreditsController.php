<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Xnova\Controller;
use Xnova\Models;

class CreditsController extends Controller
{
	public function index()
	{
		$parse = [];

		$userId = Request::post('userId') && (int) Request::post('userId') > 0 ?
			(int) Request::post('userId') : $this->user->getId();

		$parse['id'] = $userId;
		$parse['payment'] = false;

		if (Request::post('summ')) {
			$summ = (int) Request::post('summ', 0);

			do {
				$id = mt_rand(1000000000000, 9999999999999);
			} while (DB::selectOne("SELECT id FROM payments WHERE transaction_id = " . $id));

			$info = Models\Account::query()
				->find($this->user->getId(), ['email']);

			$parse['payment'] = [
				'id' => $id,
				'hash' => md5(config('settings.robokassa.login') . ":" . $summ . ":" . $id . ":" . config('settings.robokassa.public') . ":Shp_UID=" . $parse['id']),
				'summ' => $summ,
				'email' => $info->email,
				'merchant' => config('settings.robokassa.login'),
			];
		}

		$this->setTitle('Покупка кредитов');
		$this->showTopPanel(false);

		return $parse;
	}
}
