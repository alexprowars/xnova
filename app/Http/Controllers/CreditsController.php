<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Http\Request;
use Xnova\Controller;
use Xnova\Models;

class CreditsController extends Controller
{
	public function index(Request $request)
	{
		$parse = [];

		$userId = $request->post('userId') && (int) $request->post('userId') > 0 ?
			(int) $request->post('userId') : $this->user->getId();

		$parse['id'] = $userId;
		$parse['payment'] = false;

		if ($request->post('summ')) {
			$summ = (int) $request->post('summ', 0);

			do {
				$id = mt_rand(1000000000000, 9999999999999);
			} while (Models\Payment::query()->where('transaction_id', $id)->exists());

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
