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
 * @RoutePrefix("/git")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class GitController extends Controller
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
		$git_history = [];

		exec("cd ".ROOT_PATH." && git log -20", $git_logs);

		$last_hash = null;

		foreach ($git_logs as $line)
		{
		    $line = trim($line);

		    if (!empty($line))
		    {
		        if (strpos($line, 'commit') !== false)
		        {
		            $hash = explode(' ', $line);
		            $hash = trim(end($hash));

		            $git_history[$hash] = [
		                'message' => ''
		            ];

		            $last_hash = $hash;
		        }
		        elseif (strpos($line, 'Author') !== false)
				{
		            $author = explode(':', $line);
		            $author = trim(end($author));

		            $git_history[$last_hash]['author'] = htmlspecialchars($author);
		        }
		        elseif (strpos($line, 'Date') !== false)
				{
		            $date = explode(':', $line, 2);
		            $date = trim(end($date));

		            $git_history[$last_hash]['date'] = date('d.m.Y H:i:s', strtotime($date));
		        }
		        else
		            $git_history[$last_hash]['message'] .= $line ."<br>";
		    }
		}

		$this->view->setVar('history', $git_history);

		$this->tag->setTitle('История изменений');
		$this->showTopPanel(false);
	}
}