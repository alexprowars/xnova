<?php

namespace App\Controllers;

use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

class NewsController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		strings::includeLang('news');
	}
	
	public function show ()
	{
		$news = array();

		foreach (_getText('news') as $a => $b)
		{
			$news[] = array($a, nl2br($b));
		}

		$this->setTemplate('news');
		$this->set('parse', $news);

		$this->setTitle('Новости');
		$this->showTopPanel(false);
		$this->showLeftPanel(isset(user::get()->data['id']));
		$this->display();
	}
}

?>