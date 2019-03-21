<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Options;
use Xnova\Controller;
use Xnova\Request;

/**
 * @RoutePrefix("/stat")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 */
class StatController extends Controller
{
	private $field = '';
	private $page = 1;
	private $pid = 0;

	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->page = (int) $this->request->get('range', 'int', 0);
		$this->page = max($this->page, 1);

		$this->pid = (int) $this->request->getQuery('pid', 'int', 0);

		$type = (int) $this->request->get('type', 'int', 1);
		$view = $this->request->get('мшуц', 'string', 'players');

		if ($view != 'players' && $type > 5)
			$type = 1;

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
	}
	
	public function indexAction ()
	{
		$this->dispatcher->forward(["action" => "players"]);
	}

	public function playersAction ()
	{
		$type = (int) $this->request->get('type', 'int', 1);

		$parse = [
			'update' => $this->game->datezone("d.m.Y - H:i:s", Options::get('stat_update', 0)),
			'list' => $this->dispatcher->getActionName(),
			'type' => $type,
			'page' => 1
		];

		if (!$this->page && $this->getDI()->has('user'))
		{
			$records = $this->cache->get('app::records_'.$this->user->getId().'');

			if ($records === null)
			{
				$records = $this->db->query("SELECT `build_points`, `tech_points`, `fleet_points`, `defs_points`, `total_points`, `total_old_rank`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->getId() . "';")->fetch();

				if (!is_array($records))
					$records = [];

				$this->cache->save('app::records_'.$this->user->getId().'', $records, 1800);
			}

			if (isset($records[$this->field.'_rank']))
				$this->page = $records[$this->field.'_rank'];
		}

		$users = (int) Options::get('active_users', 0);

		$parse['elements'] = $users;
		$parse['page'] = $this->page;

		$position = ($parse['page'] - 1) * 100;

		if ($type == 6 || $type == 7)
			$query = $this->db->query("SELECT u.username, u.race, u.id as id_owner, a.name as ally_name, u.ally_id as id_ally, u.lvl_".$this->field." as ".$this->field."_points, 0 as ".$this->field."_old_rank FROM game_users u LEFT JOIN game_alliance a ON a.id = u.ally_id WHERE 1 = 1 ORDER BY u.lvl_".$this->field." DESC, u.xp".$this->field." DESC LIMIT " . $position . ", 100");
		else
			$query = $this->db->query("SELECT s.*, u.username, u.race FROM game_statpoints s LEFT JOIN game_users u ON u.id = s.id_owner WHERE s.stat_type = '1' AND s.stat_code = '1' AND s.stat_hide = 0 ORDER BY s." . $this->field . "_rank ASC LIMIT " . $position . ", 100");

		$position++;

		$parse['items'] = [];

		while ($item = $query->fetch())
		{
			$row = [];
			$row['id'] = (int) $item['id_owner'];
			$row['position'] = $position;

			$oldPosition = (int) $item[$this->field.'_old_rank'];

			if ($oldPosition == 0)
				$oldPosition = $position;

			$row['diff'] = $oldPosition - $position;
			$row['name'] = $item['username'];
			$row['name_marked'] = ($this->auth->isAuthorized() && $item['id_owner'] == $this->user->id) || $item['id_owner'] == $this->pid;

			$row['alliance'] = false;

			if ($item['id_ally'])
			{
				$row['alliance'] = [
					'id' => (int) $item['id_ally'],
					'name' => $item['ally_name'],
					'marked' => $this->auth->isAuthorized() && $item['ally_name'] == $this->user->ally_name
				];
			}

			$row['race'] = (int) $item['race'];
			$row['points'] = (int) $item[$this->field.'_points'];

			$parse['items'][] = $row;

			$position++;
		}

		Request::addData('page', $parse);
	}

	public function alliancesAction ()
	{
		$type = (int) $this->request->get('type', 'int', 1);

		$parse = [
			'update' => $this->game->datezone("d.m.Y - H:i:s", Options::get('stat_update', 0)),
			'list' => $this->dispatcher->getActionName(),
			'type' => $type,
			'page' => 1
		];

		$alliances = (int) Options::get('active_alliance', 0);

		$parse['elements'] = $alliances;
		$parse['page'] = $this->page;

		$position = ($parse['page'] - 1) * 100;

		$query = $this->db->query("SELECT s.*, a.`id` as ally_id, a.`tag`, a.`name`, a.`members` FROM game_statpoints s, game_alliance a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.id_owner ORDER BY s.`" . $this->field . "_rank` ASC LIMIT " . $position . ",100;");

		$position++;

		$parse['items'] = [];

		while ($item = $query->fetch())
		{
			$row = [];
			$row['id'] = (int) $item['ally_id'];
			$row['position'] = $position;

			$oldPosition = (int) $item[$this->field.'_old_rank'];

			if ($oldPosition == 0)
				$oldPosition = $position;

			$row['diff'] = $oldPosition - $position;
			$row['name'] = $item['name'];
			$row['name_marked'] = isset($this->user) && $item['name'] == $this->user->ally_name;
			$row['members'] = (int) $item['members'];
			$row['points'] = (int) $item[$this->field.'_points'];

			$parse['items'][] = $row;

			$position++;
		}

		Request::addData('page', $parse);
	}

	public function racesAction ()
	{
		$parse = [
			'update' => $this->game->datezone("d.m.Y - H:i:s", Options::get('stat_update', 0)),
			'list' => $this->dispatcher->getActionName(),
			'type' => 0,
			'page' => 0
		];

		$query = $this->db->query("SELECT * FROM game_statpoints WHERE `stat_type` = 3 AND `stat_code` = 1 ORDER BY `" . $this->field . "_rank` ASC;");

		while ($item = $query->fetch())
		{
			$row = [];
			$row['position'] = (int) $item[$this->field.'_rank'];
			$row['race'] = (int) $item['race'];
			$row['count'] = (int) $item['total_count'];
			$row['points'] = (int) $item[$this->field.'_points'];

			$parse['items'][] = $row;
		}

		Request::addData('page', $parse);
	}
}