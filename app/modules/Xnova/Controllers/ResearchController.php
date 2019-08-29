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
 * @RoutePrefix("/research")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ResearchController extends Controller
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
		$construction = new Construction($this->user, $this->planet);
		$parse = $construction->pageResearch();

		Request::addData('page', $parse);

		$this->tag->setTitle('Исследования');
	}
}