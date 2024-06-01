<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\FleetEngine;
use App\Engine\QueueManager;
use App\Engine\Vars;
use App\Format;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;

class Spy extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$owner = User::query()->find($this->fleet->user_id);

		$TargetPlanet = Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

		if ($TargetPlanet->user_id == 0) {
			$this->fleet->return();
			return false;
		}

		$targetUser = User::find($TargetPlanet->user_id);

		if (!$targetUser) {
			$this->fleet->return();

			return false;
		}

		$TargetPlanet->setRelation('user', $targetUser);
		$TargetPlanet->getProduction($this->fleet->start_time)->update();

		$queueManager = new QueueManager($targetUser, $TargetPlanet);
		$queueManager->checkUnitQueue();

		$CurrentSpyLvl = $owner->getTechLevel('spy');

		if ($owner->rpg_technocrate > time()) {
			$CurrentSpyLvl += 2;
		}

		$TargetSpyLvl = $targetUser->getTechLevel('spy');

		if ($targetUser->rpg_technocrate > time()) {
			$TargetSpyLvl += 2;
		}

		$LS = 0;

		$fleetData = $this->fleet->getShips();

		if (isset($fleetData[210])) {
			$LS = $fleetData[210]['count'];
		}

		if ($LS > 0) {
			$defenders = Fleet::find([
				'colums' => 'fleet_array',
				'conditions' => 'end_galaxy = ?0 AND end_system = ?1 AND end_planet = ?2 AND end_type = ?3 AND mess = 3',
				'bind' => [$this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, $this->fleet->end_type]
			]);

			foreach ($defenders as $row) {
				$fleetData = $row->getShips();

				foreach ($fleetData as $shipId => $shipData) {
					if ($shipId < 100) {
						continue;
					}

					$TargetPlanet->updateAmount($shipId, $shipData['count'], true);
				}
			}

			$ST = 0;

			$techDifference = abs($CurrentSpyLvl - $TargetSpyLvl);

			if ($TargetSpyLvl > $CurrentSpyLvl) {
				$ST = ($LS - ($techDifference ** 2));
			}
			if ($CurrentSpyLvl >= $TargetSpyLvl) {
				$ST = ($LS + ($techDifference ** 2));
			}

			$MaterialsInfo = $this->spyTarget($TargetPlanet, 0, __('fleet_engine.sys_spy_maretials'));
			$SpyMessage = $MaterialsInfo['String'];

			$PlanetFleetInfo = $this->spyTarget($TargetPlanet, 1, __('fleet_engine.sys_spy_fleet'));

			if ($ST >= 2) {
				$SpyMessage .= $PlanetFleetInfo['String'];
			}
			if ($ST >= 3) {
				$PlanetDefenInfo = $this->spyTarget($TargetPlanet, 2, __('fleet_engine.sys_spy_defenses'));
				$SpyMessage .= $PlanetDefenInfo['String'];
			}
			if ($ST >= 5) {
				$PlanetBuildInfo = $this->spyTarget($TargetPlanet, 3, __('main.tech.0'));
				$SpyMessage .= $PlanetBuildInfo['String'];
			}
			if ($ST >= 7) {
				$TargetTechnInfo = $this->spyTarget($targetUser, 4, __('main.tech.100'));
				$SpyMessage .= $TargetTechnInfo['String'];
			}
			if ($ST >= 9) {
				$TargetOfficierLvlInfo = $this->spyTarget($targetUser, 6, __('main.tech.600'));
				$SpyMessage .= $TargetOfficierLvlInfo['String'];
			}

			$TargetForce = ($PlanetFleetInfo['Count'] * $LS) / 4;
			$TargetForce = min(100, max(0, $TargetForce));

			$TargetChances = random_int(0, $TargetForce);
			$SpyerChances = random_int(0, 100);

			if ($TargetChances <= $SpyerChances) {
				$DestProba = sprintf(__('fleet_engine.sys_mess_spy_lostproba'), $TargetChances);
			} else {
				$DestProba = "<font color=\"red\">" . __('fleet_engine.sys_mess_spy_destroyed') . "</font>";
			}

			$AttackLink = "<center>";
			$AttackLink .= "<a href=\"/fleet/g" . $this->fleet->end_galaxy . "/s" . $this->fleet->end_system . "/";
			$AttackLink .= "p" . $this->fleet->end_planet . "/t" . $this->fleet->end_type . "/";
			$AttackLink .= "m" . $this->fleet->end_type . "/";
			$AttackLink .= " \">" . __('main.type_mission.1') . "";
			$AttackLink .= "</a></center>";

			$MessageEnd = "<center>" . $DestProba . "</center>";

			$fleet_link = '';

			if ($ST == 2) {
				$res = Vars::getItemsByType(Vars::ITEM_TYPE_FLEET);
			} elseif ($ST >= 3 && $ST <= 6) {
				$res = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);
			} elseif ($ST >= 7) {
				$res = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE, Vars::ITEM_TYPE_TECH]);
			} else {
				$res = [];
			}

			foreach ($res as $id) {
				if ($TargetPlanet->getLevel($id) > 0) {
					$fleet_link .= $id . ',' . $TargetPlanet->getLevel($id) . '!' . ((isset($targetUser->{'fleet_' . $id}) && $ST >= 8) ? $targetUser->{'fleet_' . $id} : 0) . ';';
				}

				if ($targetUser->getTechLevel($id) > 0) {
					$fleet_link .= $id . ',' . $targetUser->getTechLevel($id) . '!' . (($id > 400 && $targetUser->getTechLevel($id - 50) && $ST >= 8) ? $targetUser->getTechLevel($id - 50) : 0) . ';';
				}
			}

			if ($fleet_link != '') {
				$MessageEnd .= "<center>";
				$MessageEnd .= '<a href="/sim/' . $fleet_link . '/" target="' . config('settings.view.openRaportInNewWindow', 0) == 1 ? '_blank' : '' . '">';
				$MessageEnd .= "Симуляция</a></center>";
			}

			$MessageEnd .= "<center><a href=\"#\" onclick=\"raport_to_bb('sp" . $this->fleet->start_time->getTimestamp() . "')\">BB-код</a></center>";

			$SpyMessage = "<div id=\"sp" . $this->fleet->start_time->getTimestamp() . "\">" . $SpyMessage . "</div><br />" . $MessageEnd . $AttackLink;

			User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 1, __('fleet_engine.sys_mess_spy_report'), $SpyMessage);

			$TargetMessage  = __('fleet_engine.sys_mess_spy_ennemyfleet') . " " . $this->fleet->user_name . " ";
			$TargetMessage .= $this->fleet->getStartAdressLink();
			$TargetMessage .= __('fleet_engine.sys_mess_spy_seen_at') . " " . $TargetPlanet->name;
			$TargetMessage .= " [" . $TargetPlanet->galaxy . ":" . $TargetPlanet->system . ":" . $TargetPlanet->planet . "]. ";
			$TargetMessage .= sprintf(__('fleet_engine.sys_mess_spy_lostproba'), $TargetChances) . ".";

			User::sendMessage($TargetPlanet->user_id, 0, $this->fleet->start_time, 1, __('fleet_engine.sys_mess_spy_activity'), $TargetMessage);

			if ($TargetChances > $SpyerChances) {
				$mission = new Attack($this->fleet);
				$mission->targetEvent();
			} else {
				$this->fleet->return();
			}
		} else {
			$this->fleet->return();
		}

		return true;
	}

	public function endStayEvent()
	{
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}

	private function spyTarget(User|Planet $TargetPlanet, $Mode, $TitleString)
	{
		$LookAtLoop = true;
		$String = '';
		$Loops = 0;
		$ResFrom = [];
		$ResTo = [];

		if ($Mode == 0) {
			$String .= "<table width=\"100%\"><tr><td class=\"c\" colspan=\"4\">";
			$String .= $TitleString . " " . $TargetPlanet->name;
			$String .= " <a href=\"/galaxy/?galaxy=" . $TargetPlanet->galaxy . "&system=" . $TargetPlanet->system . "\">";
			$String .= "[" . $TargetPlanet->galaxy . ":" . $TargetPlanet->system . ":" . $TargetPlanet->planet . "]</a> ";

			if ($targetUser = $TargetPlanet->user) {
				$String .= ' <a href="/players/' . $targetUser->id . '/">' . $targetUser->username . '</a>';
			}

			$String .= "<br>на #DATE|H:i:s|" . time() . "#</td>";
			$String .= "</tr><tr>";
			$String .= "<th width=25%>Металл:</th><th width=25%>" . Format::number($TargetPlanet->metal) . "</th>";
			$String .= "<th width=25%>Кристалл:</th><th width=25%>" . Format::number($TargetPlanet->crystal) . "</th>";
			$String .= "</tr><tr>";
			$String .= "<th width=25%>Дейтерий:</th><th width=25%>" . Format::number($TargetPlanet->deuterium) . "</th>";
			$String .= "<th width=25%>Энергия:</th><th width=25%>" . Format::number($TargetPlanet->energy_max) . "</th>";
			$String .= "</tr>";
			$LookAtLoop = false;
		} elseif ($Mode == 1) {
			$ResFrom[0] = 200;
			$ResTo[0] = 299;
			$Loops = 1;
		} elseif ($Mode == 2) {
			$ResFrom[0] = 400;
			$ResTo[0] = 499;
			$ResFrom[1] = 500;
			$ResTo[1] = 599;
			$Loops = 2;
		} elseif ($Mode == 3) {
			$ResFrom[0] = 1;
			$ResTo[0] = 99;
			$Loops = 1;
		} elseif ($Mode == 4) {
			$ResFrom[0] = 100;
			$ResTo[0] = 199;
			$Loops = 1;
		} elseif ($Mode == 6) {
			$ResFrom[0] = 600;
			$ResTo[0] = 607;
			$Loops = 1;
		}

		if ($LookAtLoop) {
			$String = "<table width=\"100%\" cellspacing=\"1\"><tr><td class=\"c\" colspan=\"" . ((2 * config('settings.spyReportRow', 1)) + (config('settings.spyReportRow', 1) - 2)) . "\">" . $TitleString . "</td></tr>";
			$Count = 0;
			$CurrentLook = 0;

			while ($CurrentLook < $Loops) {
				$row = 0;

				for ($Item = $ResFrom[$CurrentLook]; $Item <= $ResTo[$CurrentLook]; $Item++) {
					if (Vars::getName($Item) === false) {
						continue;
					}

					$level = 0;
					$type = Vars::getItemType($Item);

					if ($type == Vars::ITEM_TYPE_BUILING) {
						$level = $TargetPlanet->getLevel($Item);
					} elseif ($type == Vars::ITEM_TYPE_FLEET || $type == Vars::ITEM_TYPE_DEFENSE) {
						$level = $TargetPlanet->getLevel($Item);
					} elseif ($type == Vars::ITEM_TYPE_OFFICIER) {
						$level = $TargetPlanet->{Vars::getName($Item)};
					} elseif ($type == Vars::ITEM_TYPE_TECH) {
						$level = $TargetPlanet->getTechLevel($Item);
					}

					if (($level && $Item < 600) || ($level > time() && $Item > 600)) {
						if ($row == 0) {
							$String .= "<tr>";
						}

						$String .= "<th width=40%>" . __('main.tech.' . $Item) . "</th><th width=10%>" . (($Item < 600) ? $level : '+') . "</th>";

						$Count += $Item < 600 ? $level : 1;
						$row++;

						if ($row == config('settings.spyReportRow', 1)) {
							$String .= "</tr>";
							$row = 0;
						}
					}
				}

				while ($row != 0) {
					$String .= "<th width=40%>&nbsp;</th><th width=10%>&nbsp;</th>";
					$row++;

					if ($row == config('settings.spyReportRow', 1)) {
						$String .= "</tr>";
						$row = 0;
					}
				}

				$CurrentLook++;
			}

			if ($Count == 0) {
				$String .= "<tr><th>нет данных</th></tr>";
			}
		} else {
			$Count = 0;
		}

		$String .= "</table>";

		$return['String'] = $String;
		$return['Count'] = $Count;

		return $return;
	}
}
