<?php

namespace App\Controllers;

use App\Lang;

class JumpgateController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();

		Lang::includeLang('infos');
	}

	public function indexAction ()
	{
		return false;

		/*if ($_POST && ($this->planet->planet_type == 3 || $this->planet->planet_type == 5) && $this->planet->sprungtor > 0)
		{
			$RestString = GetNextJumpWaitTime($this->planet->data);
			$NextJumpTime = $RestString['value'];
			$JumpTime = time();

			if ($NextJumpTime == 0)
			{
				$TargetPlanet = intval($_POST['jmpto']);
				$TargetGate = $this->db->query("SELECT `id`, `planet_type`, `sprungtor`, `last_jump_time` FROM game_planets WHERE `id` = '" . $TargetPlanet . "';")->fetch();

				if (($TargetGate['planet_type'] == 3 || $TargetGate['planet_type'] == 5) && $TargetGate['sprungtor'] > 0)
				{
					$RestString = GetNextJumpWaitTime($TargetGate);
					$NextDestTime = $RestString['value'];
					if ($NextDestTime == 0)
					{
						$ShipArray = array();
						$SubQueryOri = "";
						$SubQueryDes = "";

						foreach ($this->game->reslist['fleet'] AS $Ship)
						{
							$ShipLabel = "c" . $Ship;

							if (!isset($_POST[$ShipLabel]))
								continue;

							if (intval($_POST[$ShipLabel]) < 0)
							{
								die();
							}

							if (abs(intval($_POST[$ShipLabel])) > $this->planet->{$this->game->resource[$Ship]})
							{
								$ShipArray[$Ship] = $this->planet->{$this->game->resource[$Ship]};
							}
							else
							{
								$ShipArray[$Ship] = abs(intval($_POST[$ShipLabel]));
							}
							if ($ShipArray[$Ship] <> 0)
							{
								$SubQueryOri .= "`" . $this->game->resource[$Ship] . "` = `" . $this->game->resource[$Ship] . "` - '" . $ShipArray[$Ship] . "', ";
								$SubQueryDes .= "`" . $this->game->resource[$Ship] . "` = `" . $this->game->resource[$Ship] . "` + '" . $ShipArray[$Ship] . "', ";
							}
						}

						if ($SubQueryOri != "")
						{
							$QryUpdateOri = "UPDATE game_planets SET ";
							$QryUpdateOri .= $SubQueryOri;
							$QryUpdateOri .= "`last_jump_time` = '" . $JumpTime . "' ";
							$QryUpdateOri .= "WHERE ";
							$QryUpdateOri .= "`id` = '" . $this->planet->id . "';";
							$this->db->query($QryUpdateOri);

							$QryUpdateDes = "UPDATE game_planets SET ";
							$QryUpdateDes .= $SubQueryDes;
							$QryUpdateDes .= "`last_jump_time` = '" . $JumpTime . "' ";
							$QryUpdateDes .= "WHERE ";
							$QryUpdateDes .= "`id` = '" . $TargetGate['id'] . "';";
							$this->db->query($QryUpdateDes);

							$QryUpdateUsr = "UPDATE game_users SET ";
							$QryUpdateUsr .= "`planet_current` = '" . $TargetGate['id'] . "' ";
							$QryUpdateUsr .= "WHERE ";
							$QryUpdateUsr .= "`id` = '" . $this->user->id . "';";
							$this->db->query($QryUpdateUsr);

							$this->planet->last_jump_time = $JumpTime;
							$RestString = GetNextJumpWaitTime($this->planet->data);
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

		$this->message($RetMessage, _getText('tech', 43), "/infos/?gid=43", 4);*/
	}
}

?>