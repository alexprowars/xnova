<?php

namespace App\Engine\Battle;

use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Enums\ItemType;
use App\Facades\Vars;
use App\Models\Fleet;
use App\Models\User;

class Simulation
{
	protected array $slots = [];
	protected ?array $result = null;

	public function addSlot(array $items): void
	{
		$this->slots[] = $items;
	}

	public function getResult(): array
	{
		if (empty($this->result)) {
			$this->handle();
		}

		return $this->result;
	}

	public function handle(): void
	{
		$battle = new Battle();

		$this->getAttackers($battle, 0);
		$this->getAttackers($battle, $this->getMaxSlots());

		$report = $battle->run();

		$this->result = $report->toArray();
	}

	public function getMaxSlots(): int
	{
		return (int) config('game.maxSlotsInSim', 5);
	}

	public function getStatistics(): array
	{
		$battle = new Battle();

		$this->getAttackers($battle, 0);
		$this->getAttackers($battle, $this->getMaxSlots());

		$statistics = [];

		for ($i = 0; $i < 5; $i++) {
			$report = $battle->run();

			$statistics[] = [
				'att' => $report->getTotalAttackersLostUnits(),
				'def' => $report->getTotalDefendersLostUnits(),
			];

			unset($report);
		}

		uasort($statistics, fn($a, $b) => ($a['att'] > $b['att'] ? 1 : -1));

		return array_values($statistics);
	}

	private function getAttackers(Battle $battle, int $s): void
	{
		$maxSlots = $this->getMaxSlots();

		for ($i = $s; $i < $maxSlots * 2; $i++) {
			if ($i <= $maxSlots && $i < ($maxSlots + $s) && !empty($this->slots[$i])) {
				$units = [];
				$fleets = [];

				$rFleet = $this->slots[$i];

				foreach ($rFleet as $shipArr) {
					if ($shipArr['id'] > 200) {
						$fleets[$shipArr['id']] = $shipArr['count'];
					}

					$units[$shipArr['id']] = $shipArr['count'];
				}

				$user = new User(['id' => 1000 + $i, 'username' => 'Игрок ' . ($i + 1)]);

				foreach ($units as $id => $lvl) {
					if (Vars::getItemType($id) !== ItemType::TECH) {
						continue;
					}

					$user->technologies->getByEntityId($id)
						->setLevel($lvl);
				}

				$fleet = new Fleet();
				$fleet->id = 1000 + $i;
				$fleet->entities = FleetEntityCollection::createFromArray($fleets);
				$fleet->user()->associate($user);
				$fleet->end_galaxy = 1;
				$fleet->end_system = 1;
				$fleet->end_planet = 1;

				if ($s < $maxSlots) {
					$battle->addAttackerFleet($fleet);
				} else {
					$battle->addDefenderFleet($fleet);
				}
			}
		}
	}
}
