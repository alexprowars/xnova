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
use Xnova\User;
use Xnova\Models;
use Xnova\Controller;

class PaymentController extends Controller
{
	public function index ()
	{
		return [];
	}

	public function robokassa ()
	{
		if (!Input::has('InvId') || Input::get("InvId") == '' || !is_numeric(Input::get("InvId")))
			die('InvId nulled');

		$sign_hash = strtoupper(md5(Input::get('OutSum').":".Input::get('InvId').":".Config::get('game.robokassa.secret').":Shp_UID=".Input::get('Shp_UID')));

		if (strtoupper(Input::get('SignatureValue')) !== $sign_hash)
			die('signature verification failed');

		$check = DB::table('users_payments')
			->where('transaction_id', (int) Input::get("InvId"))
			->where('user', '!=', 0)
			->exists();

		if ($check)
			die('already paid');

		/** @var Models\Users $user */
		$user = Models\Users::query()->find((int) Input::get("Shp_UID"));

		if (!$user)
			die('userId not found');

		$amount = (int) Input::get('OutSum');

		if ($amount > 0)
		{
			if (!Input::has('IncCurrLabel'))
				$_REQUEST['IncCurrLabel'] = 'Free-Kassa';

			$user->credits += $amount;
			$user->save();

			DB::table('users_payments')->insert([
				'user' 				=> $user->id,
				'call_id' 			=> '',
				'method' 			=> addslashes($_REQUEST['IncCurrLabel']),
				'transaction_id' 	=> (int) Input::get("InvId"),
				'transaction_time' 	=> date("Y-m-d H:i:s", time()),
				'uid' 				=> 0,
				'amount' 			=> $amount,
				'product_code' 		=> addslashes(json_encode($_REQUEST)),
			]);

			User::sendMessage($user->id, 0, 0, 1, 'Обработка платежей', 'На ваш счет зачислено '.$amount.' кредитов');

			DB::table('log_credits')->insert([
				'uid' 		=> $user->id,
				'time' 		=> time(),
				'credits' 	=> $amount,
				'type' 		=> 1,
			]);

			echo 'OK'.Input::get("InvId");
		}
	}
}