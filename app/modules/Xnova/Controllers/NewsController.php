<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Xnova\Controller;

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
			$news[] = [$a, nl2br($b)];
		}

		$this->view->setVar('parse', $news);

		exec('cd '.ROOT_PATH.' && git rev-parse --verify HEAD 2> /dev/null', $output);

		$lastCommit = $output[0];

		$this->view->setVar('lastCommit', $lastCommit);

		$this->tag->setTitle('Новости');
		$this->showTopPanel(false);
	}
}