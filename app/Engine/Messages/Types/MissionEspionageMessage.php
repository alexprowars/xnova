<?php

namespace App\Engine\Messages\Types;

use App\Engine\Fleet\MissionType;
use App\Engine\Game;
use App\Engine\Messages\AbstractMessage;
use App\Format;
use Illuminate\Support\Uri;

class MissionEspionageMessage extends AbstractMessage
{
	protected string $type = 'MissionEspionage';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_spy_report');
	}

	public function render(): string
	{
		$result = '';
		$units = [];

		foreach ($this->data['rows'] as $row) {
			if ($row['type'] == 'SpyMessageResourceRow') {
				$result .= $this->getProductionBlock($row, $this->data['date']);
			} elseif ($row['type'] == 'SpyMessageUnitsRow') {
				$result .= $this->getUnitsBlock($row);

				foreach ($row['items'] as $unit) {
					$units[$unit['id']] = $unit['lv'];
				}
			}
		}

		if (array_key_exists('chance', $this->data)) {
			$result .= '<div class="text-center mt-2">';

			if ($this->data['chance'] === null) {
				$result .= '<span style="color: red">' . __('fleet_engine.sys_mess_spy_destroyed') . '</span>';
			} else {
				$result .= sprintf(__('fleet_engine.sys_mess_spy_lostproba'), $this->data['chance']);
			}

			$result .= '</div>';
		}

		$fleetLink = '';

		foreach ($units as $id => $level) {
			if ($level <= 0 || $id < 100) {
				continue;
			}

			$fleetLink .= $id . ',' . $level . ';';
		}

		if (!empty($fleetLink)) {
			$result .= '<div class="text-center mt-2">';
			$result .= '<a href="/sim?units=' . $fleetLink . '" target="_blank">Симуляция</a>';
			$result .= '</div>';
		}

		$planet = $this->data['rows'][0]['planet'];

		$uri = new Uri('/fleet')
			->withQuery([
				'galaxy' => $planet['galaxy'] ?? null,
				'system' => $planet['system'] ?? null,
				'planet' => $planet['planet'] ?? null,
				'type' => $planet['type'] ?? null,
				'mission' => MissionType::Attack->value,
			]);

		$result .= '<div class="text-center mt-2">';
		$result .= '<a href="' . $uri . '" target="_blank">' . __('main.type_mission.1') . '</a>';
		$result .= '</div>';

		return $result;
	}

	protected function getProductionBlock(array $row, string $date): string
	{
		$result  = '<div class="block-table text-center"><div class="grid"><div class="c">';
		$result .= __($row['title']) . ' ' . $row['planet']['name'] . ' ';

		$uri = new Uri('/galaxy')
			->withQuery([
				'galaxy' => $row['planet']['galaxy'],
				'system' => $row['planet']['system'],
			]);

		$result .= '<a href="' . $uri . '">[' . $row['planet']['galaxy'] . ':' . $row['planet']['system'] . ':' . $row['planet']['planet'] . ']</a>';

		if (isset($row['user'])) {
			$result .= ' <a href="/players/' . $row['user']['id'] . '">' . $row['user']['name'] . '</a>';
		}

		$result .= '<br>на ' . Game::datezone('H:i:s', $date) . '</div>';
		$result .= '</div><div class="grid grid-cols-4">';
		$result .= '<div class="th">' . __('main.res.metal') . ':</div><div class="th c">' . Format::number($row['resources']['metal']) . '</div>';
		$result .= '<div class="th">' . __('main.res.crystal') . ':</div><div class="th c">' . Format::number($row['resources']['crystal']) . '</div>';
		$result .= '</div><div class="grid grid-cols-4">';
		$result .= '<div class="th">' . __('main.res.deuterium') . ':</div><div class="th c">' . Format::number($row['resources']['deuterium']) . '</div>';
		$result .= '<div class="th">' . __('main.res.energy') . ':</div><div class="th c">' . Format::number($row['resources']['energy']) . '</div>';
		$result .= '</div></div>';

		return $result;
	}

	public function getUnitsBlock(array $row): string
	{
		$rowCount = config('game.spyReportRow', 1);

		$result  = '<div class="block-table text-center grid grid-cols-2">';
		$result .= '<div class="c col-span-2">' . __($row['title']) . '</div>';

		if (empty($row['items'])) {
			$result .= '<div class="th col-span-2">нет данных</div>';
		} else {
			$result .= '<div class="grid grid-cols-2 col-span-2">';

			foreach ($row['items'] as $i => $unit) {
				if ($i > 0 && $i % $rowCount == 0) {
					$result .= '</div><div class="grid grid-cols-2 col-span-2">';
				}

				$result .= '<div class="grid grid-cols-5"><div class="th col-span-4">' . __('main.tech.' . $unit['id']) . '</div><div class="c">' . $unit['lv'] . '</div></div>';
			}

			if (count($row['items']) % 2 == 1) {
				$result .= '<div class="th"></div>';
			}

			$result .= '</div>';
		}

		$result .= '</div>';

		return $result;
	}
}
