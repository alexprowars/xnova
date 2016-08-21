<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Options;
use Xnova\Helpers;
use Friday\Core\Lang;
use Phalcon\Mvc\View;
use Xnova\Controller;

/**
 * @RoutePrefix("/stat")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 */
class StatController extends Controller
{
	private $field = '';
	private $range = 0;
	private $pid = 0;

	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('stat', 'xnova');

		$this->range = $this->request->get('range', 'int', 0);
		$this->pid = $this->request->getQuery('pid', 'int', 0);

		$type = $this->request->get('type', 'int', 1);
		$who = $this->dispatcher->getActionName();

		if ($who == 'index')
			$who = 'players';

		if (($who != $this->request->getPost('old_who', 'string', '') && $this->request->getPost('old_who', 'string', '') > 0) || ($type != $this->request->getPost('old_type', 'string', '') && $this->request->getPost('old_type', 'string', '') > 0))
			$this->range = 0;

		switch ($type)
		{
			case 2:
				$this->field = 'fleet';
				break;
			case 3:
				$this->field = 'tech';
				break;
			case 4:
				$this->field = 'defs';
				break;
			case 5:
				$this->field = 'build';
				break;
			case 6:
				$this->field = 'minier';
				break;
			case 7:
				$this->field = 'raid';
				break;
			default:
				$this->field = 'total';
		}

		$this->tag->setTitle('Статистика');
		$this->showTopPanel(false);

		$this->view->disableLevel(View::LEVEL_ACTION_VIEW);
	}

	private function showTop ($range = '')
	{
		$type 	= $this->request->get('type', 'int', 1);
		$who 	= $this->dispatcher->getActionName();

		if ($who == '')
			$who = 'players';

		$this->view->partial('stat/top',
		[
			'update' 	=> $this->game->datezone("d.m.Y - H:i:s", Options::get('stat_update', 0)),
			'who' 		=> $who,
			'type' 		=> $type,
			'range'		=> $range
		]);
	}
	
	public function indexAction ()
	{
		$this->dispatcher->forward(["action" => "players"]);
	}

	public function playersAction ()
	{
		$stats = $stat = [];

		if (!$this->range && $this->getDI()->has('user'))
		{
			$records = $this->cache->get('app::records_'.$this->user->getId().'');

			if ($records === NULL)
			{
				$records = $this->db->query("SELECT `build_points`, `tech_points`, `fleet_points`, `defs_points`, `total_points`, `total_old_rank`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->getId() . "';")->fetch();

				if (!is_array($records))
					$records = [];

				$this->cache->save('app::records_'.$this->user->getId().'', $records, 1800);
			}

			if (isset($records[$this->field.'_rank']))
				$this->range = $records[$this->field.'_rank'];
		}

		if (Options::get('active_users') > 100)
			$LastPage = floor(Options::get('active_users') / 100);
		else
			$LastPage = 0;

		$rangeStr = '';
		$start = max(floor(($this->range - 1) / 100), 0);

		for ($Page = 0; $Page <= $LastPage; $Page++)
		{
			$PageValue = ($Page * 100) + 1;
			$PageRange = $PageValue + 99;
			$rangeStr .= "<option value=\"" . $PageValue . "\"" . (($start == $Page) ? " SELECTED" : "") . ">" . $PageValue . "-" . $PageRange . "</option>";
		}

		$start *= 100;

		$type = $this->request->get('type', 'int', 1);

		if ($type == 6 || $type == 7)
			$query = $this->db->query("SELECT u.username, u.race, u.id as id_owner, a.name as ally_name, u.ally_id as id_ally, u.lvl_".$this->field." as ".$this->field."_points, 0 as ".$this->field."_old_rank FROM game_users u LEFT JOIN game_alliance a ON a.id = u.ally_id WHERE 1 = 1 ORDER BY u.lvl_".$this->field." DESC, u.xp".$this->field." DESC LIMIT " . $start . ", 100");
		else
			$query = $this->db->query("SELECT s.*, u.username, u.race FROM game_statpoints s LEFT JOIN game_users u ON u.id = s.id_owner WHERE s.stat_type = '1' AND s.stat_code = '1' AND s.stat_hide = 0 ORDER BY s." . $this->field . "_rank ASC LIMIT " . $start . ", 100");

		$start++;

		while ($StatRow = $query->fetch())
		{
			$stats['id'] = $StatRow['id_owner'];
			$stats['rank'] = $start;

			$rank_old = $StatRow[$this->field.'_old_rank'];
			if ($rank_old == 0)
				$rank_old = $start;

			$rank_new = $start;
			$ranking = $rank_old - $rank_new;

			if ($ranking == 0)
				$stats['rankplus'] = "<font color=\"#87CEEB\">*</font>";
			if ($ranking < 0)
				$stats['rankplus'] = "<span class=\"negative\">" . $ranking . "</span>";
			if ($ranking > 0)
				$stats['rankplus'] = "<span class=\"positive\">+" . $ranking . "</span>";

			if (($this->auth->isAuthorized() && $StatRow['id_owner'] == $this->user->id) || $StatRow['id_owner'] == $this->pid)
				$stats['name'] = "<span class=\"neutral\">" . $StatRow['username'] . "</span>";
			else
				$stats['name'] = $StatRow['username'];

			if ($this->auth->isAuthorized())
				$stats['mes'] = "<a href=\"javascript:;\" onclick=\"showWindow('" . $StatRow['username'] . ": отправить сообщение', '".$this->url->getBaseUri()."messages/write/" . $StatRow['id_owner'] . "/', 680)\" title=\"Сообщение\"><span class='sprite skin_m'></span></a>";

			if ($this->auth->isAuthorized() && $StatRow['ally_name'] == $this->user->ally_name)
				$stats['alliance'] = "<font color=\"#33CCFF\">" . $StatRow['ally_name'] . "</font>";
			elseif ($StatRow['ally_name'] != '')
				$stats['alliance'] = "<a href=\"".$this->url->getBaseUri()."alliance/info/" . $StatRow['id_ally'] . "/\">" . $StatRow['ally_name'] . "</a>";
			else
				$stats['alliance'] = '&nbsp;';

			$stats['race'] = $StatRow['race'];

			$stats['points'] = Helpers::pretty_number($StatRow[$this->field.'_points']);

			$stat[] = $stats;

			$start++;
		}

		$this->showTop($rangeStr);
		$this->view->setVar('stat', $stat);
		$this->view->partial('stat/players');
	}

	public function allianceAction ()
	{
		$stat = [];

		if (Options::get('active_alliance') > 100)
			$LastPage = floor(Options::get('active_alliance') / 100);
		else
			$LastPage = 0;

		$rangeStr = '';
		$start = max(floor(($this->range - 1) / 100), 0);

		for ($Page = 0; $Page <= $LastPage; $Page++)
		{
			$PageValue = ($Page * 100) + 1;
			$PageRange = $PageValue + 99;
			$rangeStr .= "<option value=\"" . $PageValue . "\"" . (($start == $Page) ? " SELECTED" : "") . ">" . $PageValue . "-" . $PageRange . "</option>";
		}

		$start *= 100;
		$query = $this->db->query("SELECT s.*, a.`id` as ally_id, a.`tag`, a.`name`, a.`members` FROM game_statpoints s, game_alliance a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.id_owner ORDER BY s.`" . $this->field . "_rank` ASC LIMIT " . $start . ",100;");

		$start++;

		while ($StatRow = $query->fetch())
		{
			$stats['id'] = $StatRow['ally_id'];
			$stats['rank'] = $start;
			$rank_old = $StatRow[$this->field.'_old_rank'];
			$rank_new = $start;

			$ranking = $rank_old - $rank_new;

			if ($ranking == 0)
				$stats['rankplus'] = "<font color=\"#87CEEB\">*</font>";
			if ($ranking < 0)
				$stats['rankplus'] = "<font color=\"red\">" . $ranking . "</font>";
			if ($ranking > 0)
				$stats['rankplus'] = "<font color=\"green\">+" . $ranking . "</font>";

			if (isset($this->user) && $StatRow['name'] == $this->user->ally_name)
				$stats['name'] = "<font color=\"#33CCFF\">" . $StatRow['name'] . "</font>";
			else
				$stats['name'] = "<a href=\"".$this->url->getBaseUri()."alliance/info/" . $StatRow['ally_id'] . "/\">" . $StatRow['name'] . "</a>";

			$stats['mes'] = '';
			$stats['members'] = $StatRow['members'];
			$stats['points'] = Helpers::pretty_number($StatRow[$this->field.'_points']);
			$stats['members_points'] = Helpers::pretty_number(floor($StatRow[$this->field.'_points'] / $StatRow['members']));

			$stat[] = $stats;

			$start++;
		}

		$this->showTop($rangeStr);
		$this->view->setVar('stat', $stat);
		$this->view->partial('stat/alliance');
	}

	public function raceAction ()
	{
		$stat = [];

		$rangeStr = "<option value='0'>1-4</option>";

		$query = $this->db->query("SELECT * FROM game_statpoints WHERE `stat_type` = 3 AND `stat_code` = 1 ORDER BY `" . $this->field . "_rank` ASC;");

		while ($StatRow = $query->fetch())
		{
			$stats['rank'] = $StatRow[$this->field.'_rank'];
			$stats['race'] = $StatRow['race'];
			$stats['count'] = $StatRow['total_count'];
			$stats['points'] = Helpers::pretty_number($StatRow[$this->field.'_points']);

			if ($StatRow['total_count'] > 0)
				$stats['pointatuser'] = Helpers::pretty_number(floor($StatRow[$this->field.'_points'] / $StatRow['total_count']));
			else
				$stats['pointatuser'] = 0;

			$stat[] = $stats;
		}

		$this->showTop($rangeStr);
		$this->view->setVar('stat', $stat);
		$this->view->partial('stat/race');
	}
}