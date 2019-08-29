<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Construction;
use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Exceptions\PageException;
use Xnova\Request;

/**
 * @RoutePrefix("/defense")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class DefenseController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('buildings', 'xnova');

		$this->user->loadPlanet();

		if ($this->user->vacation > 0)
			throw new PageException("Нет доступа!");
	}

	public function indexAction ()
	{
		if ($this->planet->planet_type == 5)
			$this->user->setUserOption('only_available', true);

		$construction = new Construction($this->user, $this->planet);
		$parse = $construction->pageShipyard('defense');

		$parse['mode'] = $this->dispatcher->getControllerName();
		$parse['queue'] = $construction->ElementBuildListBox();

		Request::addData('page', $parse);

		$this->tag->setTitle('Оборона');
	}
}