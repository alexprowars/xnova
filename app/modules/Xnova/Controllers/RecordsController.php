<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Options;
use Xnova\Format;
use Xnova\Controller;

/**
 * @RoutePrefix("/records")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class RecordsController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$RecordsArray = [];

		if (file_exists(ROOT_PATH.$this->config->application->baseDir.$this->config->application->cacheDir."/CacheRecords.php"))
			require_once(ROOT_PATH.$this->config->application->baseDir.$this->config->application->cacheDir."/CacheRecords.php");

		$Builds = [];
		$MoonsBuilds = [];
		$Techno = [];
		$Fleet = [];
		$Defense = [];

		foreach ($RecordsArray as $ElementID => $ElementIDArray)
		{
			if ($ElementID >= 1 && $ElementID <= 39 || $ElementID == 44)
			{
				$Builds[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 41 && $ElementID <= 99 && $ElementID != 44)
			{
				$MoonsBuilds[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 101 && $ElementID <= 199)
			{
				$Techno[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 201 && $ElementID <= 399)
			{
				$Fleet[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 401 && $ElementID <= 599)
			{
				$Defense[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			}
		}

		$Records = [
			'Постройки' => $Builds,
			'Лунные постройки' => $MoonsBuilds,
			'Исследования' => $Techno,
			'Флот' => $Fleet,
			'Оборона' => $Defense,
		];

		$parse = [
			'Records'	=> $Records,
			'update'	=> Options::get('stat_update'),
		];

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Таблица рекордов');
		$this->showTopPanel(false);
	}
}