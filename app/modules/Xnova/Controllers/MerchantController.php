<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\UserQuest;
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
	private $modifiers = [
		'metal' => 1,
		'crystal' => 2,
		'deuterium' => 4,
	];

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
		if ($this->request->hasPost('exchange'))
			$this->exchange();

		Request::addData('page', [
			'modifiers' => $this->modifiers
		]);

		$this->tag->setTitle('Торговец');
	}

	private function exchange ()
	{
		if ($this->user->credits <= 0)
			throw new ErrorException('Недостаточно кредитов для проведения обменной операции');

		$metal = (int) $this->request->getPost('metal', 'int', 0);
		$crystal = (int) $this->request->getPost('crystal', 'int', 0);
		$deuterium = (int) $this->request->getPost('deuterium', 'int', 0);

		if ($metal < 0 || $crystal < 0 || $deuterium < 0)
			throw new ErrorException('Злобный читер');

		$type = trim($this->request->getPost('type'));

		if (!in_array($type, Vars::getResources()))
			throw new ErrorException('Ресурс не существует');

		$exchange = 0;

		foreach (Vars::getResources() as $res)
		{
			if ($res != $type)
				$exchange += $$res * ($this->modifiers[$res] / $this->modifiers[$type]);
		}

		if ($exchange <= 0)
			throw new ErrorException('Вы не можете обменять такое количество ресурсов');

		if ($this->planet->{$type} < $exchange)
			throw new ErrorException('На планете недостаточно ресурсов данного типа');

		$this->planet->{$type} -= $exchange;

		foreach (Vars::getResources() as $res)
		{
			if ($res != $type)
				$this->planet->{$res} += $$res;
		}

		$this->planet->update();

		$this->user->credits -= 1;
		$this->user->update();

		/** @var UserQuest $tutorial */
		$tutorial = UserQuest::query()
			->columns(['id'])
			->where('user_id = :user: AND quest_id = 6 AND finish = 0 AND stage = 0')
			->bind(['user' => $this->user->getId()])
			->execute()->getFirst();

		if ($tutorial)
		{
			$tutorial->stage = 1;
			$tutorial->update();
		}

		throw new RedirectException('Вы обменяли '.$exchange.' '._getText('res', $type), '/merchant/');
	}
}