<?php

namespace App\Controllers;

use Xcms\core;
use Xcms\request;
use Xnova\User;
use Xnova\app;
use Xnova\pageHelper;

class TechtreeController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		app::loadPlanet();
	}
	
	public function show ()
	{
		global $requeriments, $resource;

		 $parse = array();
		
		$Element = request::G('id', 0);

		if ($Element > 0 && isset($resource[$Element]))
		{
			$this->setTemplate('techtree_element');
			$this->set('element', $Element);

			if (isset($requeriments[$Element]))
				$this->set('req', $requeriments[$Element]);

			core::setConfig('overviewListView', 0);
		}
		else
		{
			foreach (_getText('tech') as $Element => $ElementName)
			{
				if ($Element >= 300 && $Element < 400)
					continue;
		
				if ($Element < 600)
				{
					$pars = array();
					$pars['tt_name'] = $ElementName;
		
					if (!isset($resource[$Element]))
					{
						$parse[] = $pars;
					}
					else
					{
						if (isset($requeriments[$Element]))
						{
							$pars['required_list'] = "";
		
							foreach ($requeriments[$Element] as $ResClass => $Level)
							{
								if ($ResClass != 700)
								{
									if (isset(user::get()->data[$resource[$ResClass]]) && user::get()->data[$resource[$ResClass]] >= $Level)
									{
										$pars['required_list'] .= "<span class=\"positive\">";
									}
									elseif (isset(app::$planetrow->data[$resource[$ResClass]]) && app::$planetrow->data[$resource[$ResClass]] >= $Level)
									{
										$pars['required_list'] .= "<span class=\"positive\">";
									}
									else
									{
										$pars['required_list'] .= "<span class=\"negative\">";
									}
									$pars['required_list'] .= _getText('tech', $ResClass) . " (" . _getText('level') . " " . $Level . "";
		
									if (isset(user::get()->data[$resource[$ResClass]]) && user::get()->data[$resource[$ResClass]] < $Level)
									{
										$minus = $Level - user::get()->data[$resource[$ResClass]];
										$pars['required_list'] .= " + <b>" . $minus . "</b>";
									}
									elseif (isset(app::$planetrow->data[$resource[$ResClass]]) && app::$planetrow->data[$resource[$ResClass]] < $Level)
									{
										$minus = $Level - app::$planetrow->data[$resource[$ResClass]];
										$pars['required_list'] .= " + <b>" . $minus . "</b>";
									}
								}
								else
								{
									$pars['required_list'] .= _getText('tech', $ResClass) . " (";
		
									if (user::get()->data['race'] != $Level)
										$pars['required_list'] .= "<span class=\"negative\">" . _getText('race', $Level);
									else
										$pars['required_list'] .= "<span class=\"positive\">" . _getText('race', $Level);
								}
		
								$pars['required_list'] .= ")</span><br>";
							}
						}
						else
							$pars['required_list'] = "";
		
						$pars['tt_info'] = $Element;
						$parse[] = $pars;
					}
				}
			}
		
			$this->setTemplate('techtree');
		}
		
		$this->set('parse', $parse);

		$this->setTitle(_getText('Tech'));
		$this->showTopPanel(false);
		$this->display();
	}
}

?>