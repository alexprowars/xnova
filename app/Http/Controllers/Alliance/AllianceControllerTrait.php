<?php

namespace App\Http\Controllers\Alliance;

use App\Exceptions\Exception;
use App\Models\Alliance;

trait AllianceControllerTrait
{
	protected function getAlliance(): Alliance
	{
		$alliance = $this->user->alliance;

		if (!$alliance) {
			throw new Exception(__('alliance.ally_notexist'));
		}

		$alliance->getRanks();

		$member = $alliance->getMember($this->user);

		if (!$member) {
			$member = $alliance->members()->make();
			$member->user()->associate($this->user);

			if ($member->save()) {
				$alliance->member = $member;
			}
		}

		return $alliance;
	}
}
