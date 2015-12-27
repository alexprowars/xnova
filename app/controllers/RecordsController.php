<?php

namespace App\Controllers;

use Xcms\core;
use Xcms\strings;
use Xnova\pageHelper;

class RecordsController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		$RecordsArray = array();

		if (file_exists(ROOT_DIR.CACHE_DIR."/CacheRecords.php"))
			require_once(ROOT_DIR.CACHE_DIR."/CacheRecords.php");

		$Builds = array();
		$MoonsBuilds = array();
		$Techno = array();
		$Fleet = array();
		$Defense = array();

		foreach ($RecordsArray as $ElementID => $ElementIDArray)
		{
			if ($ElementID >= 1 && $ElementID <= 39 || $ElementID == 44)
			{
				$Builds[_getText('tech', $ElementID)] = array(
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? strings::pretty_number($ElementIDArray['maxlvl']) : '-',
				);
			}
			elseif ($ElementID >= 41 && $ElementID <= 99 && $ElementID != 44)
			{
				$MoonsBuilds[_getText('tech', $ElementID)] = array(
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? strings::pretty_number($ElementIDArray['maxlvl']) : '-',
				);
			}
			elseif ($ElementID >= 101 && $ElementID <= 199)
			{
				$Techno[_getText('tech', $ElementID)] = array(
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? strings::pretty_number($ElementIDArray['maxlvl']) : '-',
				);
			}
			elseif ($ElementID >= 201 && $ElementID <= 399)
			{
				$Fleet[_getText('tech', $ElementID)] = array(
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? strings::pretty_number($ElementIDArray['maxlvl']) : '-',
				);
			}
			elseif ($ElementID >= 401 && $ElementID <= 599)
			{
				$Defense[_getText('tech', $ElementID)] = array(
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? strings::pretty_number($ElementIDArray['maxlvl']) : '-',
				);
			}
		}

		$Records = array(
			'Постройки' => $Builds,
			'Лунные постройки' => $MoonsBuilds,
			'Исследования' => $Techno,
			'Флот' => $Fleet,
			'Оборона' => $Defense,
		);

		$parse = array(
			'Records' => $Records,
			'update' => core::getConfig('stat_update'),
		);

		$this->setTemplate('records');
		$this->set('parse', $parse);

		$this->setTitle('Таблица рекордов');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>