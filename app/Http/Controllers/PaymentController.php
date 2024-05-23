<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\User;
use App\Models;
use App\Controller;

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

		$sign_hash = strtoupper(md5(Request::input('OutSum') . ":" . Request::input('InvId') . ":" . config('settings.robokassa.secret') . ":Shp_UID=" . Request::input('Shp_UID')));

		if (strtoupper(Request::input('SignatureValue')) !== $sign_hash) {
			die('signature verification failed');
		}

		$check = Models\Payment::query()
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

			Models\Payment::query()->insert([
				'user_id' 			=> $user->id,
				'call_id' 			=> '',
				'method' 			=> addslashes($_REQUEST['IncCurrLabel']),
				'transaction_id' 	=> (int) Request::input("InvId"),
				'transaction_time' 	=> date("Y-m-d H:i:s", time()),
				'uid' 				=> 0,
				'amount' 			=> $amount,
				'product_code' 		=> addslashes(json_encode($_REQUEST)),
			]);

			User::sendMessage($user->id, 0, 0, 2, 'Обработка платежей', 'На ваш счет зачислено ' . $amount . ' кредитов');

			Models\LogCredit::create([
				'user_id' => $user->id,
				'amount' => $amount,
				'type' => 1,
			]);

			echo 'OK' . Request::input("InvId");
		}
	}
}
