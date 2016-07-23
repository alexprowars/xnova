<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Helpers;

class RecordsController extends Application
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$RecordsArray = [];

		if (file_exists(APP_PATH.$this->config->application->cacheDir."/CacheRecords.php"))
			require_once(APP_PATH.$this->config->application->cacheDir."/CacheRecords.php");

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
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Helpers::pretty_number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 41 && $ElementID <= 99 && $ElementID != 44)
			{
				$MoonsBuilds[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Helpers::pretty_number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 101 && $ElementID <= 199)
			{
				$Techno[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Helpers::pretty_number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 201 && $ElementID <= 399)
			{
				$Fleet[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Helpers::pretty_number($ElementIDArray['maxlvl']) : '-',
				];
			}
			elseif ($ElementID >= 401 && $ElementID <= 599)
			{
				$Defense[_getText('tech', $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Helpers::pretty_number($ElementIDArray['maxlvl']) : '-',
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
			'Records' => $Records,
			'update' => $this->config->app->get('stat_update'),
		];

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Таблица рекордов');
		$this->showTopPanel(false);
	}
}