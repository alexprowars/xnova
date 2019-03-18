<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Request;

/**
 * @RoutePrefix("/news")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class NewsController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('news', 'xnova');
	}
	
	public function indexAction ()
	{
		$news = [];

		foreach (_getText('news') as $a => $b)
		{
			$news[] = [
				'title' => $a,
				'text' => nl2br($b)
			];
		}

		Request::addData('page', [
			'items' => $news
		]);

		$this->tag->setTitle('Новости');
	}
}