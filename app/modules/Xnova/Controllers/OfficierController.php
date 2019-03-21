<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Request;
use Xnova\Vars;

/**
 * @RoutePrefix("/officier")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class OfficierController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		if ($this->user->vacation > 0)
			throw new PageException('В режиме отпуска данный раздел недоступен!');

		$this->user->loadPlanet();

		Lang::includeLang('officier', 'xnova');
	}

	public function buyAction ()
	{
		$id = (int) $this->request->getPost('id');
		$duration = (int) $this->request->getPost('duration');

		if (!$id || !$duration)
			throw new ErrorException('Ошибка входных параметров');

		switch ($duration)
		{
			case 7:
				$credits = 20;
			break;

			case 14:
				$credits = 40;
			break;

			case 30:
				$credits = 80;
			break;

			default:
				throw new ErrorException('Ошибка входных параметров');
		}

		$time = $duration * 86400;

		if (!$credits || !$time || $this->user->credits < $credits)
			throw new ErrorException(_getText('NoPoints'));

		if (Vars::getItemType($id) != Vars::ITEM_TYPE_OFFICIER)
			throw new ErrorException('Выбран неверный элемент');

		if ($this->user->{Vars::getName($id)} > time())
			$this->user->{Vars::getName($id)} = $this->user->{Vars::getName($id)} + $time;
		else
			$this->user->{Vars::getName($id)} = time() + $time;

		$this->user->credits -= $credits;
		$this->user->update();

		$this->db->insertAsDict('game_log_credits', [
			'uid' => $this->user->id,
			'time' => time(),
			'credits' => $credits * (-1),
			'type' => 5
		]);

		throw new RedirectException(_getText('OffiRecrute'), '/officier/');
	}
	
	public function indexAction ()
	{
		$parse['credits'] = (int) $this->user->credits;
		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $officier)
		{
			$row['id'] = $officier;
			$row['time'] = 0;

			if ($this->user->{Vars::getName($officier)} > time())
				$row['time'] = $this->user->{Vars::getName($officier)};

			$row['description'] = _getText('Desc', $officier);
			$row['power'] = _getText('power', $officier);

			$parse['items'][] = $row;
		}

		Request::addData('page', $parse);
		
		$this->tag->setTitle('Офицеры');
	}
}