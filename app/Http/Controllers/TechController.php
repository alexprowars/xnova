<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Xnova\Building;
use Xnova\Controller;
use Xnova\Planet\EntityFactory;
use Xnova\Vars;
use Xnova\Entity;

class TechController extends Controller
{
	protected $loadPlanet = true;

	public function index()
	{
		$this->setTitle(__('tech.Tech'));

		$parse = [];
		$items = [];

		foreach (__('main.tech') as $element => $name) {
			if ($element > 600) {
				continue;
			}

			$pars = [];
			$pars['name'] = $name;

			if (Vars::getName($element) === false) {
				if (count($items) > 0) {
					$parse[count($parse) - 1]['items'] = $items;
					$items = [];
				}

				$pars['items'] = [];
				$parse[] = $pars;
			} else {
				$pars['required'] = "";

				$requeriments = Vars::getItemRequirements($element);

				foreach ($requeriments as $ResClass => $Level) {
					if ($ResClass != 700) {
						$type = Vars::getItemType($ResClass);

						if ($type == Vars::ITEM_TYPE_TECH && $this->user->getTechLevel($ResClass) >= $Level) {
							$pars['required'] .= "<span class=\"positive\">";
						} elseif ($type == Vars::ITEM_TYPE_BUILING && $this->planet->getLevel($ResClass) >= $Level) {
							$pars['required'] .= "<span class=\"positive\">";
						} else {
							$pars['required'] .= "<span class=\"negative\">";
						}

						$pars['required'] .= __('main.tech.' . $ResClass) . " (" . __('main.level') . " " . $Level . "";

						if ($type == Vars::ITEM_TYPE_TECH && $this->user->getTechLevel($ResClass) < $Level) {
							$minus = $Level - $this->user->getTechLevel($ResClass);
							$pars['required'] .= " + <b>" . $minus . "</b>";
						} elseif ($type == Vars::ITEM_TYPE_BUILING && $this->planet->getLevel($ResClass) < $Level) {
							$minus = $Level - $this->planet->getLevel($ResClass);
							$pars['required'] .= " + <b>" . $minus . "</b>";
						}
					} else {
						$pars['required'] .= __('main.tech.' . $ResClass) . " (";

						if ($this->user->race != $Level) {
							$pars['required'] .= "<span class=\"negative\">" . __('main.race.' . $Level);
						} else {
							$pars['required'] .= "<span class=\"positive\">" . __('main.race.' . $Level);
						}
					}

					$pars['required'] .= ")</span><br>";
				}

				$pars['info'] = $element;
				$items[] = $pars;
			}
		}

		if (!count($parse[count($parse) - 1]['items'])) {
			unset($parse[count($parse) - 1]);
		}

		$this->showTopPanel(false);

		return $parse;
	}

	public function info($element)
	{
		$page = [];

		$element = (int) $element;

		if ($element > 0 && Vars::getName($element)) {
			$entity = EntityFactory::create($element, 1, $this->planet);

			$page['element'] = $element;
			$page['level'] = $this->user->getTechLevel($element) ? $this->user->getTechLevel($element) : $this->planet->getLevel($element);
			$page['access'] = $entity->isAvailable();
			$page['req'] = Vars::getItemRequirements($element);

			$this->setTitle(__('main.tech.' . $element));
		}

		$data = [
			0 => []
		];

		$storage = Vars::getStorage();

		foreach ($storage['resource'] as $id => $code) {
			$data[$id] = [
				'name' => __('main.tech.' . $id),
				'img' => $id . '.gif',
				'req' => [],
			];

			if (isset($storage['requeriments'][$id]) && count($storage['requeriments'][$id])) {
				foreach ($storage['requeriments'][$id] as $ids => $level) {
					$data[$id]['req'][] = [
						$ids,
						__('main.tech.' . $ids),
						$level,
						-1,
						$level
					];
				}
			}
		}

		$page['data'] = $data;

		$this->showTopPanel(false);

		return $page;
	}
}
