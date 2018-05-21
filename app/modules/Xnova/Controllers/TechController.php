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
		$items = [];

		foreach (_getText('tech') as $element => $name)
		{
			if ($element >= 600)
				continue;

			$pars = [];
			$pars['name'] = $name;

			if (Vars::getName($element) === false)
			{
				if (count($items) > 0)
				{
					$parse[count($parse) - 1]['items'] = $items;
					$items = [];
				}

				$pars['items'] = [];
				$parse[] = $pars;
			}
			else
			{
				$pars['required'] = "";

				$requeriments = Vars::getItemRequirements($element);

				foreach ($requeriments as $ResClass => $Level)
				{
					if ($ResClass != 700)
					{
						$type = Vars::getItemType($ResClass);

						if ($type == Vars::ITEM_TYPE_TECH && $this->user->getTechLevel($ResClass) >= $Level)
							$pars['required'] .= "<span class=\"positive\">";
						elseif ($type == Vars::ITEM_TYPE_BUILING && $this->planet->getBuildLevel($ResClass) >= $Level)
							$pars['required'] .= "<span class=\"positive\">";
						else
							$pars['required'] .= "<span class=\"negative\">";

						$pars['required'] .= _getText('tech', $ResClass) . " (" . _getText('level') . " " . $Level . "";

						if ($type == Vars::ITEM_TYPE_TECH && $this->user->getTechLevel($ResClass) < $Level)
						{
							$minus = $Level - $this->user->getTechLevel($ResClass);
							$pars['required'] .= " + <b>" . $minus . "</b>";
						}
						elseif ($type == Vars::ITEM_TYPE_BUILING && $this->planet->getBuildLevel($ResClass) < $Level)
						{
							$minus = $Level - $this->planet->getBuildLevel($ResClass);
							$pars['required'] .= " + <b>" . $minus . "</b>";
						}
					}
					else
					{
						$pars['required'] .= _getText('tech', $ResClass) . " (";

						if ($this->user->race != $Level)
							$pars['required'] .= "<span class=\"negative\">" . _getText('race', $Level);
						else
							$pars['required'] .= "<span class=\"positive\">" . _getText('race', $Level);
					}

					$pars['required'] .= ")</span><br>";
				}

				$pars['info'] = $element;
				$items[] = $pars;
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