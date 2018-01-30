<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Vars;

/**
 * @RoutePrefix("/tech")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class TechController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();

		$this->tag->setTitle(_getText('Tech'));
	}
	
	public function indexAction ()
	{
		$parse = [];

		foreach (_getText('tech') as $element => $name)
		{
			if ($element < 600)
			{
				$pars = [];
				$pars['tt_name'] = $name;

				if (Vars::getName($element) === false)
					$parse[] = $pars;
				else
				{
					$pars['required_list'] = "";

					$requeriments = Vars::getItemRequeriments($element);

					foreach ($requeriments as $ResClass => $Level)
					{
						if ($ResClass != 700)
						{
							if ($this->user->getTechLevel($ResClass) >= $Level)
								$pars['required_list'] .= "<span class=\"positive\">";
							elseif ($this->planet->getBuildLevel($ResClass) >= $Level)
								$pars['required_list'] .= "<span class=\"positive\">";
							else
								$pars['required_list'] .= "<span class=\"negative\">";

							$pars['required_list'] .= _getText('tech', $ResClass) . " (" . _getText('level') . " " . $Level . "";

							if ($this->user->getTechLevel($ResClass) < $Level)
							{
								$minus = $Level - $this->user->getTechLevel($ResClass);
								$pars['required_list'] .= " + <b>" . $minus . "</b>";
							}
							elseif ($this->planet->getBuildLevel($ResClass) < $Level)
							{
								$minus = $Level - $this->planet->getBuildLevel($ResClass);
								$pars['required_list'] .= " + <b>" . $minus . "</b>";
							}
						}
						else
						{
							$pars['required_list'] .= _getText('tech', $ResClass) . " (";

							if ($this->user->race != $Level)
								$pars['required_list'] .= "<span class=\"negative\">" . _getText('race', $Level);
							else
								$pars['required_list'] .= "<span class=\"positive\">" . _getText('race', $Level);
						}

						$pars['required_list'] .= ")</span><br>";
					}

					$pars['tt_info'] = $element;
					$parse[] = $pars;
				}
			}
		}
		
		$this->view->setVar('parse', $parse);
		$this->showTopPanel(false);
	}

	/**
	 * @Route("/{element:[0-9]+}{params:(/.*)*}")
	 * @param $element
	 */
	public function infoAction ($element)
	{
		$element = (int) $element;

		if ($element > 0 && Vars::getName($element))
		{
			$this->view->setVar('element', $element);
			$this->view->setVar('level', $this->user->getTechLevel($element) ? $this->user->getTechLevel($element) : $this->planet->getBuildLevel($element));

			if (isset($this->registry->requeriments[$element]))
				$this->view->setVar('req', $this->registry->requeriments[$element]);

			$this->tag->setTitle(_getText('tech')[$element]);
		}

		$this->showTopPanel(false);
	}
}