<?php

namespace App\Http\Controllers;

use App\Models\Referal;

class ReferralsController extends Controller
{
	public function index()
	{
		$referals = Referal::query()
			->whereBelongsTo($this->user, 'user')
			->orderByDesc('referal_id')
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

		$refers = Referal::query()
			->whereBelongsTo($this->user, 'referal')
			->with('user')
			->first();

		if ($refers) {
			$parse['you'] = [
				'id' => $refers->user->id,
				'username' => $refers->user->username,
			];
		}

		return $parse;
	}
}
