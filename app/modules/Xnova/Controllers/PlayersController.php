<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Format;
use Xnova\Models\Planet;
use Xnova\Controller;
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

		$ownid = ($this->auth->isAuthorized()) ? $this->user->id : 0;
		
		$PlayerCard = $this->db->query("SELECT u.*, ui.about, ui.image FROM game_users u LEFT JOIN game_users_info ui ON ui.id = u.id WHERE ".(is_numeric($playerid) ? "u.id" : "u.username")." = '" . $playerid . "';");
		
		if ($daten = $PlayerCard->fetch())
		{
			if ($daten['image'] != '')
				$parse['avatar'] = "assets/avatars/".$daten['image'];
			elseif ($daten['avatar'] != 0)
			{
				if ($daten['avatar'] != 99)
					$parse['avatar'] = "assets/images/faces/".$daten['sex']."/" . $daten['avatar'] . ".png";
				else
					$parse['avatar'] = "assets/avatars/upload_" . $daten['id'] . ".jpg";
			}
			else
				$parse['avatar'] = 'assets/images/no_photo.gif';

			$parse['avatar'] = $this->url->getBaseUri().$parse['avatar'];

			$gesamtkaempfe = $daten['raids_win'] + $daten['raids_lose'];

			if ($gesamtkaempfe == 0)
			{
				$siegprozent = 0;
				$loosprozent = 0;
			}
			else
			{
				$siegprozent = 100 / $gesamtkaempfe * $daten['raids_win'];
				$loosprozent = 100 / $gesamtkaempfe * $daten['raids_lose'];
			}

			$parse['userplanet'] = '';

			$planet = Planet::findByCoords($daten['galaxy'], $daten['system'], $daten['planet'], 1);

			if ($planet)
				$parse['userplanet'] = $planet->name;
		
			$points = $this->db->query("SELECT * FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $daten['id'] . "';")->fetch();
			$parse['tech_rank'] = Format::number($points['tech_rank']);
			$parse['tech_points'] = Format::number($points['tech_points']);
			$parse['build_rank'] = Format::number($points['build_rank']);
			$parse['build_points'] = Format::number($points['build_points']);
			$parse['fleet_rank'] = Format::number($points['fleet_rank']);
			$parse['fleet_points'] = Format::number($points['fleet_points']);
			$parse['defs_rank'] = Format::number($points['defs_rank']);
			$parse['defs_points'] = Format::number($points['defs_points']);
			$parse['total_rank'] = Format::number($points['total_rank']);
			$parse['total_points'] = Format::number($points['total_points']);
		
			if ($ownid != 0)
				$parse['player_buddy'] = "<a href=\"".$this->url->getBaseUri()."buddy/new/" . $playerid . "/\" title=\"Добавить в друзья\">Добавить в друзья</a>";
			else
				$parse['player_buddy'] = "";
		
			if ($ownid != 0)
				$parse['player_mes'] = "<a href=\"".$this->url->getBaseUri()."messages/write/" . $playerid . "/\">Написать сообщение</a>";
			else
				$parse['player_mes'] = "";
		
			if ($daten['sex'] == 2)
				$parse['sex'] = "Женский";
			else
				$parse['sex'] = "Мужской";

			$parse['ingame'] = ($ownid != 0) ? true : false;
			$parse['id'] = $daten['id'];
			$parse['username'] = $daten['username'];
			$parse['race'] = $daten['race'];
			$parse['galaxy'] = $daten['galaxy'];
			$parse['system'] = $daten['system'];
			$parse['planet'] = $daten['planet'];
			$parse['ally_id'] = $daten['ally_id'];
			$parse['ally_name'] = $daten['ally_name'];
			$parse['about'] = $daten['about'];
			$parse['wons'] = Format::number($daten['raids_win']);
			$parse['loos'] = Format::number($daten['raids_lose']);
			$parse['siegprozent'] = round($siegprozent, 2);
			$parse['loosprozent'] = round($loosprozent, 2);
			$parse['total'] = $daten['raids'];
			$parse['totalprozent'] = 100;
			$parse['m'] = User::getRankId($daten['lvl_minier']);
			$parse['f'] = User::getRankId($daten['lvl_raid']);
		}
		else
			throw new ErrorException('Параметр задан неверно');

		$this->view->setVar('parse', $parse);

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
		$parse['data'] = $this->db->extractResult($this->db->query("SELECT * FROM game_log_stats WHERE id = ".$playerid." AND type = 1 AND time > ".(time() - 14 * 86400)." ORDER BY time ASC"));

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Статистика игрока');
		$this->showTopPanel(false);
		$this->showLeftPanel($this->auth->isAuthorized());
	}
}