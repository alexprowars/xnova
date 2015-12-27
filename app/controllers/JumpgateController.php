<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\strings;
use Xnova\User;
use Xnova\app;
use Xnova\pageHelper;

class JumpgateController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		app::loadPlanet();

		strings::includeLang('infos');
	}

	public function show ()
	{
		global $resource, $reslist;

		die();

		if ($_POST && (app::$planetrow->data['planet_type'] == 3 || app::$planetrow->data['planet_type'] == 5) && app::$planetrow->data['sprungtor'] > 0)
		{
			$RestString = GetNextJumpWaitTime(app::$planetrow->data);
			$NextJumpTime = $RestString['value'];
			$JumpTime = time();

			if ($NextJumpTime == 0)
			{
				$TargetPlanet = intval($_POST['jmpto']);
				$TargetGate = db::query("SELECT `id`, `planet_type`, `sprungtor`, `last_jump_time` FROM game_planets WHERE `id` = '" . $TargetPlanet . "';", true);

				if (($TargetGate['planet_type'] == 3 || $TargetGate['planet_type'] == 5) && $TargetGate['sprungtor'] > 0)
				{
					$RestString = GetNextJumpWaitTime($TargetGate);
					$NextDestTime = $RestString['value'];
					if ($NextDestTime == 0)
					{
						$ShipArray = array();
						$SubQueryOri = "";
						$SubQueryDes = "";

						foreach ($reslist['fleet'] AS $Ship)
						{
							$ShipLabel = "c" . $Ship;

							if (!isset($_POST[$ShipLabel]))
								continue;

							if (intval($_POST[$ShipLabel]) < 0)
							{
								die();
							}

							if (abs(intval($_POST[$ShipLabel])) > app::$planetrow->data[$resource[$Ship]])
							{
								$ShipArray[$Ship] = app::$planetrow->data[$resource[$Ship]];
							}
							else
							{
								$ShipArray[$Ship] = abs(intval($_POST[$ShipLabel]));
							}
							if ($ShipArray[$Ship] <> 0)
							{
								$SubQueryOri .= "`" . $resource[$Ship] . "` = `" . $resource[$Ship] . "` - '" . $ShipArray[$Ship] . "', ";
								$SubQueryDes .= "`" . $resource[$Ship] . "` = `" . $resource[$Ship] . "` + '" . $ShipArray[$Ship] . "', ";
							}
						}

						if ($SubQueryOri != "")
						{
							$QryUpdateOri = "UPDATE game_planets SET ";
							$QryUpdateOri .= $SubQueryOri;
							$QryUpdateOri .= "`last_jump_time` = '" . $JumpTime . "' ";
							$QryUpdateOri .= "WHERE ";
							$QryUpdateOri .= "`id` = '" . app::$planetrow->data['id'] . "';";
							db::query($QryUpdateOri);

							$QryUpdateDes = "UPDATE game_planets SET ";
							$QryUpdateDes .= $SubQueryDes;
							$QryUpdateDes .= "`last_jump_time` = '" . $JumpTime . "' ";
							$QryUpdateDes .= "WHERE ";
							$QryUpdateDes .= "`id` = '" . $TargetGate['id'] . "';";
							db::query($QryUpdateDes);

							$QryUpdateUsr = "UPDATE game_users SET ";
							$QryUpdateUsr .= "`current_planet` = '" . $TargetGate['id'] . "' ";
							$QryUpdateUsr .= "WHERE ";
							$QryUpdateUsr .= "`id` = '" . user::get()->data['id'] . "';";
							db::query($QryUpdateUsr);

							app::$planetrow->data['last_jump_time'] = $JumpTime;
							$RestString = GetNextJumpWaitTime(app::$planetrow->data);
							$RetMessage = _getText('gate_jump_done') . " - " . $RestString['string'];
						}
						else
						{
							$RetMessage = _getText('gate_wait_data');
						}
					}
					else
					{
						$RetMessage = _getText('gate_wait_dest') . " - " . $RestString['string'];
					}
				}
				else
				{
					$RetMessage = _getText('gate_no_dest_g');
				}
			}
			else
			{
				$RetMessage = _getText('gate_wait_star') . " - " . $RestString['string'];
			}
		}
		else
		{
			$RetMessage = _getText('gate_wait_data');
		}

		$this->message($RetMessage, _getText('tech', 43), "?set=infos&gid=43", 4);
	}
}

?>