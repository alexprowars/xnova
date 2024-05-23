<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Controller;
use App\Models;

class CreditsController extends Controller
{
	public function index(Request $request)
	{
		$parse = [];

		$userId = $request->post('userId') && (int) $request->post('userId') > 0 ?
			(int) $request->post('userId') : $this->user->id;

		$parse['id'] = $userId;
		$parse['payment'] = false;

		if ($request->post('summ')) {
			$summ = (int) $request->post('summ', 0);

			do {
				$id = random_int(1000000000000, 9999999999999);
			} while (Models\Payment::query()->where('transaction_id', $id)->exists());

			$parse['payment'] = [
				'id' => $id,
				'hash' => md5(config('settings.robokassa.login') . ":" . $summ . ":" . $id . ":" . config('settings.robokassa.public') . ":Shp_UID=" . $parse['id']),
				'summ' => $summ,
				'email' => $this->user->email,
				'merchant' => config('settings.robokassa.login'),
			];
		}

		return $parse;
	}
}
