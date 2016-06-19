<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Lang;

class NewsController extends Application
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('news');
	}
	
	public function indexAction ()
	{
		$news = [];

		foreach (_getText('news') as $a => $b)
		{
			$news[] = [$a, nl2br($b)];
		}

		$this->view->setVar('parse', $news);

		exec('cd '.APP_PATH.' && git rev-parse --verify HEAD 2> /dev/null', $output);

		$lastCommit = $output[0];

		$this->view->setVar('lastCommit', $lastCommit);

		$this->tag->setTitle('Новости');
		$this->showTopPanel(false);
	}
}