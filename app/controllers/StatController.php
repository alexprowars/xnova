<?php

namespace App\Controllers;

use Xcms\cache;
use Xcms\core;
use Xcms\db;
use Xcms\request;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

class StatController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		strings::includeLang('stat');
	}
	
	public function show ()
	{
		$parse = array();

		$who 	= request::R('who', 1, VALUE_INT);
		$type 	= request::R('type', 1, VALUE_INT);
		$range 	= request::R('range', 0, VALUE_INT);
		$pid 	= request::G('pid', 0, VALUE_INT);

		if (($who != request::P('old_who', 0, VALUE_INT) && request::P('old_who', 0, VALUE_INT) > 0) || ($type != request::P('old_type', 0, VALUE_INT) && request::P('old_type', 0, VALUE_INT) > 0))
			$range = 0;

		switch ($type)
		{
			case 2:
				$field = 'fleet';
				break;
			case 3:
				$field = 'tech';
				break;
			case 4:
				$field = 'defs';
				break;
			case 5:
				$field = 'build';
				break;
			default:
				$field = 'total';
		}

		$this->setTemplate('stat');
		$this->set('who', $who);
		$this->set('type', $type);

		$stat = array();

		if ($who == 3)
		{
			$this->setTemplate('stat_race');

			$parse['range'] = "<option value='0'>1-4</option>";

			$query = db::query("SELECT * FROM game_statpoints WHERE `stat_type` = 3 AND `stat_code` = 1 ORDER BY `" . $field . "_rank` ASC;");

			while ($StatRow = db::fetch_assoc($query))
			{
				$stats['player_rank'] = $StatRow[$field.'_rank'];
				$stats['player_race'] = $StatRow['race'];
				$stats['player_count'] = $StatRow['total_count'];
				$stats['player_points'] = strings::pretty_number($StatRow[$field.'_points']);
				$stats['player_pointatuser'] = strings::pretty_number(floor($StatRow[$field.'_points'] / $StatRow['total_count']));

				$stat[] = $stats;
			}
		}
		elseif ($who == 2)
		{
			$this->setTemplate('stat_alliance');
			$stat = array();

			if (core::getConfig('active_alliance') > 100)
				$LastPage = floor(core::getConfig('active_alliance') / 100);
			else
				$LastPage = 0;

			$parse['range'] = "";
			$start = max(floor(($range - 1) / 100), 0);

			for ($Page = 0; $Page <= $LastPage; $Page++)
			{
				$PageValue = ($Page * 100) + 1;
				$PageRange = $PageValue + 99;
				$parse['range'] .= "<option value=\"" . $PageValue . "\"" . (($start == $Page) ? " SELECTED" : "") . ">" . $PageValue . "-" . $PageRange . "</option>";
			}

			$start *= 100;
			$query = db::query("SELECT s.*, a.`id`, a.`ally_tag`, a.`ally_name`, a.`ally_members` FROM game_statpoints s, game_alliance a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.id_owner ORDER BY s.`" . $field . "_rank` ASC LIMIT " . $start . ",100;");

			$start++;

			while ($StatRow = db::fetch_assoc($query))
			{
				$stats['ally_id'] = $StatRow['id'];
				$stats['ally_rank'] = $start;
				$rank_old = $StatRow[$field.'_old_rank'];
				$rank_new = $start;

				$ranking = $rank_old - $rank_new;

				if ($ranking == 0)
					$stats['ally_rankplus'] = "<font color=\"#87CEEB\">*</font>";
				if ($ranking < 0)
					$stats['ally_rankplus'] = "<font color=\"red\">" . $ranking . "</font>";
				if ($ranking > 0)
					$stats['ally_rankplus'] = "<font color=\"green\">+" . $ranking . "</font>";

				if ($StatRow['ally_name'] == user::get()->data['ally_name'])
					$stats['ally_name'] = "<font color=\"#33CCFF\">" . $StatRow['ally_name'] . "</font>";
				else
					$stats['ally_name'] = "<a href=\"?set=alliance&mode=ainfo&a=" . $StatRow['id'] . "\">" . $StatRow['ally_name'] . "</a>";

				$stats['ally_mes'] = '';
				$stats['ally_members'] = $StatRow['ally_members'];
				$stats['ally_points'] = strings::pretty_number($StatRow[$field.'_points']);
				$stats['ally_members_points'] = strings::pretty_number(floor($StatRow[$field.'_points'] / $StatRow['ally_members']));

				$stat[] = $stats;

				$start++;
			}
		}
		else
		{
			$this->setTemplate('stat_players');
			$stats = array();

			if (!$range)
			{
				$records = cache::get('app::records_'.user::get()->getId().'');

				if ($records === false)
				{
					$records = db::query("SELECT `build_points`, `tech_points`, `fleet_points`, `defs_points`, `total_points`, `total_old_rank`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . user::get()->getId() . "';", true);

					if (!is_array($records))
						$records = array();

					cache::set('app::records_'.user::get()->getId().'', $records, 1800);
				}

				if (isset($records[$field.'_rank']))
					$range = $records[$field.'_rank'];
			}

			if (core::getConfig('active_users') > 100)
				$LastPage = floor(core::getConfig('active_users') / 100);
			else
				$LastPage = 0;

			$parse['range'] = "";
			$start = max(floor(($range - 1) / 100), 0);

			for ($Page = 0; $Page <= $LastPage; $Page++)
			{
				$PageValue = ($Page * 100) + 1;
				$PageRange = $PageValue + 99;
				$parse['range'] .= "<option value=\"" . $PageValue . "\"" . (($start == $Page) ? " SELECTED" : "") . ">" . $PageValue . "-" . $PageRange . "</option>";
			}

			$start *= 100;

			$query = db::query("SELECT * FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `stat_hide` = 0 ORDER BY `" . $field . "_rank` ASC LIMIT " . $start . ",100;");

			$start++;

			while ($StatRow = db::fetch_assoc($query))
			{
				$stats['player_id'] = $StatRow['id_owner'];
				$stats['player_rank'] = $start;

				$rank_old = $StatRow[$field.'_old_rank'];
				if ($rank_old == 0)
					$rank_old = $start;

				$rank_new = $start;
				$ranking = $rank_old - $rank_new;

				if ($ranking == 0)
					$stats['player_rankplus'] = "<font color=\"#87CEEB\">*</font>";
				if ($ranking < 0)
					$stats['player_rankplus'] = "<span class=\"negative\">" . $ranking . "</span>";
				if ($ranking > 0)
					$stats['player_rankplus'] = "<span class=\"positive\">+" . $ranking . "</span>";

				if ((user::get()->isAuthorized() && $StatRow['id_owner'] == user::get()->data['id']) || $StatRow['id_owner'] == $pid)
					$stats['player_name'] = "<span class=\"neutral\">" . $StatRow['username'] . "</span>";
				else
					$stats['player_name'] = $StatRow['username'];

				if (user::get()->isAuthorized())
					$stats['player_mes'] = "<a href=\"javascript:;\" onclick=\"showWindow('" . $StatRow['username'] . ": отправить сообщение', '?set=messages&mode=write&id=" . $StatRow['id_owner'] . "&ajax&popup', 680)\" title=\"Сообщение\"><span class='sprite skin_m'></span></a>";

				if (user::get()->isAuthorized() && $StatRow['ally_name'] == user::get()->data['ally_name'])
					$stats['player_alliance'] = "<font color=\"#33CCFF\">" . $StatRow['ally_name'] . "</font>";
				elseif ($StatRow['ally_name'] != '')
					$stats['player_alliance'] = "<a href=\"?set=alliance&mode=ainfo&a=" . $StatRow['id_ally'] . "\">" . $StatRow['ally_name'] . "</a>";
				else
					$stats['player_alliance'] = '&nbsp;';

				$stats['player_race'] = $StatRow['race'];

				$stats['player_points'] = strings::pretty_number($StatRow[$field.'_points']);

				$stat[] = $stats;

				$start++;
			}
		}

		$parse['stat_date'] = datezone("d.m.Y - H:i:s", core::getConfig('stat_update'));

		$this->set('stat', $stat);
		$this->setTemplateName('stat');
		$this->set('parse', $parse);

		$this->setTitle('Статистика');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>