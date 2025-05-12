<?php

namespace App\Http\Controllers\Alliance;

use App\Exceptions\Exception;
use App\Models\Alliance;
use App\Models\AllianceMember;

trait AllianceControllerTrait
{
	protected function getAlliance(): Alliance
	{
		$alliance = $this->user->alliance;

		if (!$alliance) {
			throw new Exception('Alliance not found');
		}

		$alliance->getRanks();

		$member = $alliance->getMember($this->user);

		if (!$member) {
			$member = new AllianceMember();
			$member->user()->associate($this->user);
			$member->save();

			if ($member = $alliance->members()->save($member)) {
				$alliance->member = $member;
			}
		}

		return $alliance;
	}
}
