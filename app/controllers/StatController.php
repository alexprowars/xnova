<?php

namespace App\Controllers;

use App\Helpers;
use App\Lang;

class StatController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('stat');
	}
	
	public function indexAction ()
	{
		$parse = array();

		$who 	= $this->request->get('who', 'int', 1);
		$type 	= $this->request->get('type', 'int', 1);
		$range 	= $this->request->get('range', 'int', 0);
		$pid 	= $this->request->getQuery('pid', 'int', 0);

		if (($who != $this->request->getPost('old_who', 'int', 0) && $this->request->getPost('old_who', 'int', 0) > 0) || ($type != $this->request->getPost('old_type', 'int', 0) && $this->request->getPost('old_type', 'int', 0) > 0))
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

		$this->view->pick('stat');
		$this->view->setVar('who', $who);
		$this->view->setVar('type', $type);

		$stat = array();

		if ($who == 3)
		{
			$this->view->pick('stat_race');

			$parse['range'] = "<option value='0'>1-4</option>";

			$query = $this->db->query("SELECT * FROM game_statpoints WHERE `stat_type` = 3 AND `stat_code` = 1 ORDER BY `" . $field . "_rank` ASC;");

			while ($StatRow = $query->fetch())
			{
				$stats['player_rank'] = $StatRow[$field.'_rank'];
				$stats['player_race'] = $StatRow['race'];
				$stats['player_count'] = $StatRow['total_count'];
				$stats['player_points'] = Helpers::pretty_number($StatRow[$field.'_points']);
				$stats['player_pointatuser'] = Helpers::pretty_number(floor($StatRow[$field.'_points'] / $StatRow['total_count']));

				$stat[] = $stats;
			}
		}
		elseif ($who == 2)
		{
			$this->view->pick('stat_alliance');
			$stat = array();

			if ($this->config->app->get('active_alliance') > 100)
				$LastPage = floor($this->config->app->get('active_alliance') / 100);
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
			$query = $this->db->query("SELECT s.*, a.`id`, a.`ally_tag`, a.`ally_name`, a.`ally_members` FROM game_statpoints s, game_alliance a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.id_owner ORDER BY s.`" . $field . "_rank` ASC LIMIT " . $start . ",100;");

			$start++;

			while ($StatRow = $query->fetch())
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

				if ($StatRow['ally_name'] == $this->user->ally_name)
					$stats['ally_name'] = "<font color=\"#33CCFF\">" . $StatRow['ally_name'] . "</font>";
				else
					$stats['ally_name'] = "<a href=\"/alliance/?mode=ainfo&a=" . $StatRow['id'] . "\">" . $StatRow['ally_name'] . "</a>";

				$stats['ally_mes'] = '';
				$stats['ally_members'] = $StatRow['ally_members'];
				$stats['ally_points'] = Helpers::pretty_number($StatRow[$field.'_points']);
				$stats['ally_members_points'] = Helpers::pretty_number(floor($StatRow[$field.'_points'] / $StatRow['ally_members']));

				$stat[] = $stats;

				$start++;
			}
		}
		else
		{
			$this->view->pick('stat_players');
			$stats = array();

			if (!$range)
			{
				$records = $this->cache->get('app::records_'.$this->user->getId().'');

				if ($records === NULL)
				{
					$records = $this->db->query("SELECT `build_points`, `tech_points`, `fleet_points`, `defs_points`, `total_points`, `total_old_rank`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->getId() . "';")->fetch();

					if (!is_array($records))
						$records = array();

					$this->cache->save('app::records_'.$this->user->getId().'', $records, 1800);
				}

				if (isset($records[$field.'_rank']))
					$range = $records[$field.'_rank'];
			}

			if ($this->config->app->get('active_users') > 100)
				$LastPage = floor($this->config->app->get('active_users') / 100);
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

			$query = $this->db->query("SELECT * FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `stat_hide` = 0 ORDER BY `" . $field . "_rank` ASC LIMIT " . $start . ",100;");

			$start++;

			while ($StatRow = $query->fetch())
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

				if (($this->auth->isAuthorized() && $StatRow['id_owner'] == $this->user->id) || $StatRow['id_owner'] == $pid)
					$stats['player_name'] = "<span class=\"neutral\">" . $StatRow['username'] . "</span>";
				else
					$stats['player_name'] = $StatRow['username'];

				if ($this->auth->isAuthorized())
					$stats['player_mes'] = "<a href=\"javascript:;\" onclick=\"showWindow('" . $StatRow['username'] . ": отправить сообщение', '/messages/?mode=write&id=" . $StatRow['id_owner'] . "&ajax&popup', 680)\" title=\"Сообщение\"><span class='sprite skin_m'></span></a>";

				if ($this->auth->isAuthorized() && $StatRow['ally_name'] == $this->user->ally_name)
					$stats['player_alliance'] = "<font color=\"#33CCFF\">" . $StatRow['ally_name'] . "</font>";
				elseif ($StatRow['ally_name'] != '')
					$stats['player_alliance'] = "<a href=\"/alliance/?mode=ainfo&a=" . $StatRow['id_ally'] . "\">" . $StatRow['ally_name'] . "</a>";
				else
					$stats['player_alliance'] = '&nbsp;';

				$stats['player_race'] = $StatRow['race'];

				$stats['player_points'] = Helpers::pretty_number($StatRow[$field.'_points']);

				$stat[] = $stats;

				$start++;
			}
		}

		$parse['stat_date'] = $this->game->datezone("d.m.Y - H:i:s", $this->config->app->get('stat_update'));

		$this->view->setVar('stat', $stat);
		$this->view->pick('stat');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Статистика');
		$this->showTopPanel(false);
	}
}

?>