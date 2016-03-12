<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class TechController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();

		$this->tag->setTitle(_getText('Tech'));
	}
	
	public function indexAction ()
	{
		$parse = [];

		foreach (_getText('tech') as $Element => $ElementName)
		{
			if ($Element >= 300 && $Element < 400)
				continue;

			if ($Element < 600)
			{
				$pars = [];
				$pars['tt_name'] = $ElementName;

				if (!isset($this->storage->resource[$Element]))
					$parse[] = $pars;
				else
				{
					if (isset($this->storage->requeriments[$Element]))
					{
						$pars['required_list'] = "";

						foreach ($this->storage->requeriments[$Element] as $ResClass => $Level)
						{
							if ($ResClass != 700)
							{
								if (isset($this->user->{$this->storage->resource[$ResClass]}) && $this->user->{$this->storage->resource[$ResClass]} >= $Level)
									$pars['required_list'] .= "<span class=\"positive\">";
								elseif (isset($this->planet->{$this->storage->resource[$ResClass]}) && $this->planet->{$this->storage->resource[$ResClass]} >= $Level)
									$pars['required_list'] .= "<span class=\"positive\">";
								else
									$pars['required_list'] .= "<span class=\"negative\">";

								$pars['required_list'] .= _getText('tech', $ResClass) . " (" . _getText('level') . " " . $Level . "";

								if (isset($this->user->{$this->storage->resource[$ResClass]}) && $this->user->{$this->storage->resource[$ResClass]} < $Level)
								{
									$minus = $Level - $this->user->{$this->storage->resource[$ResClass]};
									$pars['required_list'] .= " + <b>" . $minus . "</b>";
								}
								elseif (isset($this->planet->{$this->storage->resource[$ResClass]}) && $this->planet->{$this->storage->resource[$ResClass]} < $Level)
								{
									$minus = $Level - $this->planet->{$this->storage->resource[$ResClass]};
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
					}
					else
						$pars['required_list'] = "";

					$pars['tt_info'] = $Element;
					$parse[] = $pars;
				}
			}
		}
		
		$this->view->setVar('parse', $parse);
		$this->showTopPanel(false);
	}

	public function infoAction ()
	{
		$Element = $this->request->getQuery('id', 'int', 0);

		if ($Element > 0 && isset($this->storage->resource[$Element]))
		{
			$this->view->setVar('element', $Element);

			if (isset($this->storage->requeriments[$Element]))
				$this->view->setVar('req', $this->storage->requeriments[$Element]);

			$this->config->view->offsetSet('overviewListView', 0);

			$this->tag->setTitle(_getText('tech')[$Element]);
		}

		$this->showTopPanel(false);
	}
}