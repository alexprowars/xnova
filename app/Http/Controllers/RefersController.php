<?php

namespace App\Http\Controllers;

use App\Models\Referal;
use Illuminate\Support\Facades\DB;

class RefersController extends Controller
{
	public function index()
	{
		$referals = Referal::query()
			->where('u_id', $this->user->id)
			->orderByDesc('r_id')
			->with('referal')
			->get();

		$parse['items'] = [];

		foreach ($referals as $referal) {
			$parse['items'][] = [
				'id' => $referal->referal->id,
				'username' => $referal->referal->username,
				'lvl_minier' => $referal->referal->lvl_minier,
				'lvl_raid' => $referal->referal->lvl_raid,
				'created_at' => $referal->referal->created_at->utc()->toAtomString(),
			];
		}

		$refers = DB::selectOne("SELECT u.id, u.username FROM referals r LEFT JOIN users u ON u.id = r.u_id WHERE r.r_id = " . $this->user->id . "");

		if ($refers) {
			$parse['you'] = (array) $refers;
		}

		return $parse;
	}
}
