<?php

namespace App\Http\Controllers\Alliance;

use App\Models\AllianceMember;

trait AllianceControllerTrait
{
	protected function getAlliance()
	{
		$alliance = $this->user->alliance;
		$alliance->getRanks();

		$member = $alliance->getMember($this->user->id);

		if (!$member) {
			$member = new AllianceMember();
			$member->user_id = $this->user->id;
			$member->save();

			if ($member = $alliance->members()->save($member)) {
				$alliance->member = $member;
			}
		}

		return $alliance;
	}
}
