<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Xnova\User;
use Xnova\Models;
use Xnova\Controller;

class PaymentController extends Controller
{
	public function index()
	{
		return [];
	}

	public function robokassa()
	{
		if (!Request::has('InvId') || Request::input("InvId") == '' || !is_numeric(Request::input("InvId"))) {
			die('InvId nulled');
		}

		$sign_hash = strtoupper(md5(Request::input('OutSum') . ":" . Request::input('InvId') . ":" . config('game.robokassa.secret') . ":Shp_UID=" . Request::input('Shp_UID')));

		if (strtoupper(Request::input('SignatureValue')) !== $sign_hash) {
			die('signature verification failed');
		}

		$check = DB::table('payments')
			->where('transaction_id', (int) Request::input("InvId"))
			->where('user_id', '!=', 0)
			->exists();

		if ($check) {
			die('already paid');
		}

		$user = Models\User::query()
			->find((int) Request::input("Shp_UID"));

		if (!$user) {
			die('userId not found');
		}

		$amount = (int) Request::input('OutSum');

		if ($amount > 0) {
			if (!Request::has('IncCurrLabel')) {
				$_REQUEST['IncCurrLabel'] = 'Free-Kassa';
			}

			$user->credits += $amount;
			$user->save();

			DB::table('payments')->insert([
				'user_id' 			=> $user->id,
				'call_id' 			=> '',
				'method' 			=> addslashes($_REQUEST['IncCurrLabel']),
				'transaction_id' 	=> (int) Request::input("InvId"),
				'transaction_time' 	=> date("Y-m-d H:i:s", time()),
				'uid' 				=> 0,
				'amount' 			=> $amount,
				'product_code' 		=> addslashes(json_encode($_REQUEST)),
			]);

			User::sendMessage($user->id, 0, 0, 1, 'Обработка платежей', 'На ваш счет зачислено ' . $amount . ' кредитов');

			DB::table('log_credits')->insert([
				'uid' 		=> $user->id,
				'time' 		=> time(),
				'credits' 	=> $amount,
				'type' 		=> 1,
			]);

			echo 'OK' . Request::input("InvId");
		}
	}
}
