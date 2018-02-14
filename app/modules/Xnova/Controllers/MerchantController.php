<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Exceptions\MessageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Request;
use Xnova\Vars;

/**
 * @RoutePrefix("/merchant")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class MerchantController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		Lang::includeLang('marchand', 'xnova');

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		$parse = [];

		$parse['mod'] = [
			'metal' => 1,
			'crystal' => 2,
			'deuterium' => 4,
		];
		
		if ($this->request->hasPost('exchange'))
		{
			if ($this->user->credits <= 0)
				throw new RedirectException('Недостаточно кредитов для проведения обменной операции', 'Ошибка', '/merchant/', 3);
		
			$metal = (int) $this->request->getPost('metal', 'int', 0);
			$crystal = (int) $this->request->getPost('crystal', 'int', 0);
			$deuterium = (int) $this->request->getPost('deuterium', 'int', 0);

			if ($metal < 0 || $crystal < 0 || $deuterium < 0)
				throw new MessageException('Злобный читер');

			$type = trim($this->request->getPost('type'));

			if (!in_array($type, Vars::getResources()))
				throw new MessageException('Злобный читер');

			$exchange = 0;

			foreach (Vars::getResources() as $res)
			{
				if ($res != $type)
					$exchange += $$res * ($parse['mod'][$res] / $parse['mod'][$type]);
			}

			if ($exchange <= 0)
				throw new MessageException('Вы не можете обменять такое количество ресурсов');

			if ($this->planet->{$type} < $exchange)
				throw new MessageException('На планете недостаточно ресурсов данного типа');

			$this->planet->{$type} -= $exchange;

			foreach (Vars::getResources() as $res)
			{
				if ($res != $type)
					$this->planet->{$res} += $$res;
			}

			$this->planet->update();

			$this->user->credits -= 1;
			$this->user->update();

			$tutorial = $this->db->query("SELECT id FROM game_users_quests WHERE user_id = ".$this->user->getId()." AND quest_id = 6 AND finish = '0' AND stage = 0")->fetch();

			if (isset($tutorial['id']))
				$this->db->query("UPDATE game_users_quests SET stage = 1 WHERE id = " . $tutorial['id'] . ";");

			throw new RedirectException('Вы обменяли '.$exchange.' '._getText('res', $type), 'Обмен прошёл успешно', '/merchant/', 2);
		}

		Request::addData('page', $parse);

		$this->tag->setTitle('Торговец');
	}
}