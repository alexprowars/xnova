<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Files;
use Xnova\Exceptions\ErrorException;
use Xnova\Format;
use Xnova\Models\Planet;
use Xnova\Controller;
use Xnova\Request;
use Xnova\User;

/**
 * @RoutePrefix("/players")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class PlayersController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	/**
	 * @Route("/{id:[0-9]+}{params:(/.*)*}")
	 */
	public function indexAction ()
	{
		$parse = [];
		
		$playerid = htmlspecialchars($this->request->get('id', null, ''));

		if (!$playerid)
			throw new ErrorException('Профиль не найден');
		
		$user = $this->db->query("SELECT u.*, ui.about, ui.image FROM game_users u LEFT JOIN game_users_info ui ON ui.id = u.id WHERE ".(is_numeric($playerid) ? "u.id" : "u.username")." = '" . $playerid . "';")->fetch();
		
		if (!$user)
			throw new ErrorException('Профиль не найден');

		$parse['avatar'] = 'assets/images/no_photo.gif';

		if ($user['image'] > 0)
		{
			$file = Files::getById($user['image']);

			if ($file)
				$parse['avatar'] = $file['src'];
		}
		elseif ($user['avatar'] != 0)
		{
			if ($user['avatar'] != 99)
				$parse['avatar'] = "assets/images/faces/".$user['sex']."/" . $user['avatar'] . ".png";
		}

		$parse['avatar'] = $this->url->getStatic($parse['avatar']);
		$parse['userplanet'] = '';

		$planet = Planet::findByCoords($user['galaxy'], $user['system'], $user['planet'], 1);

		if ($planet)
			$parse['userplanet'] = $planet->name;

		$parse['stats'] = false;

		$points = $this->db->query("SELECT * FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $user['id'] . "';")->fetch();

		if ($points)
		{
			$parse['stats'] = [
				'tech_rank' => (int) $points['tech_rank'] ?? 0,
				'tech_points' => (int) $points['tech_points'] ?? 0,
				'build_rank' => (int) $points['build_rank'] ?? 0,
				'build_points' => (int) $points['build_points'] ?? 0,
				'fleet_rank' => (int) $points['fleet_rank'] ?? 0,
				'fleet_points' => (int) $points['fleet_points'] ?? 0,
				'defs_rank' => (int) $points['defs_rank'] ?? 0,
				'defs_points' => (int) $points['defs_points'] ?? 0,
				'total_rank' => (int) $points['total_rank'] ?? 0,
				'total_points' => (int) $points['total_points'] ?? 0,
			];
		}

		$parse['sex'] = (int) $user['sex'];
		$parse['id'] = (int) $user['id'];
		$parse['username'] = $user['username'];
		$parse['race'] = (int) $user['race'];
		$parse['galaxy'] = (int) $user['galaxy'];
		$parse['system'] = (int) $user['system'];
		$parse['planet'] = (int) $user['planet'];
		$parse['ally_id'] = (int) $user['ally_id'];
		$parse['ally_name'] = $user['ally_name'];
		$parse['about'] = preg_replace("/(\r\n)/u", "<br>", stripslashes($user['about']));
		$parse['wons'] = (int) $user['raids_win'];
		$parse['loos'] = (int) $user['raids_lose'];
		$parse['total'] = (int) $user['raids'];

		$parse['m'] = User::getRankId($user['lvl_minier']);
		$parse['f'] = User::getRankId($user['lvl_raid']);

		Request::addData('page', $parse);

		$this->tag->setTitle('Информация о игроке');
		$this->showTopPanel(false);
		$this->showLeftPanel($this->auth->isAuthorized());
	}

	/**
	 * @Route("/stat/{id:[0-9]+}{params:(/.*)*}")
	 */
	public function statAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->indexAction();

		$playerid = $this->request->get('id', null, 0);

		$player = $this->db->query("SELECT id, username FROM game_users WHERE id = ".$playerid."")->fetch();

		if (!isset($player['id']))
			throw new ErrorException('Информация о данном игроке не найдена');

		$parse = [];
		$parse['name'] = $player['username'];
		$parse['points'] = [];

		$items = $this->db->query("SELECT * FROM game_log_stats WHERE object_id = ".$playerid." AND type = 1 AND time > ".(time() - 14 * 86400)." ORDER BY time ASC");

		while ($item = $items->fetch())
		{
			$parse['points'][] = [
				'date' => (int) $item['time'],
				'rank' => [
					'tech' => (int) $item['tech_rank'],
					'build' => (int) $item['build_rank'],
					'defs' => (int) $item['defs_rank'],
					'fleet' => (int) $item['fleet_rank'],
					'total' => (int) $item['total_rank'],
				],
				'point' => [
					'tech' => (int) $item['tech_points'],
					'build' => (int) $item['build_points'],
					'defs' => (int) $item['defs_points'],
					'fleet' => (int) $item['fleet_points'],
					'total' => (int) $item['total_points'],
				]
			];
		}

		Request::addData('page', $parse);

		$this->tag->setTitle('Статистика игрока');
		$this->showTopPanel(false);
		$this->showLeftPanel($this->auth->isAuthorized());
	}
}