<?php

namespace App\Http\Controllers;

use App\Models\AllianceMember;
use Illuminate\Support\Facades\URL;

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

	protected function MessageForm($Title, $Message, $Goto = '', $Button = ' ok ', $TwoLines = false)
	{
		$Form = "<form action=\"" . URL::to(ltrim($Goto, '/')) . "\" method=\"post\">";
		$Form .= "<table width=\"100%\"><tr>";
		$Form .= "<td class=\"c\">" . $Title . "</td>";
		$Form .= "</tr><tr>";

		if ($TwoLines == true) {
			$Form .= "<th >" . $Message . "</th>";
			$Form .= "</tr><tr>";
			$Form .= "<th align=\"center\"><input type=\"submit\" value=\"" . $Button . "\"></th>";
		} else {
			$Form .= "<th>" . $Message . "<input type=\"submit\" value=\"" . $Button . "\"></th>";
		}

		$Form .= "</tr></table></form>";

		return $Form;
	}
}
