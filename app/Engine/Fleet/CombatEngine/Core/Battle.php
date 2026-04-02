<?php

namespace App\Engine\Fleet\CombatEngine\Core;

use App\Engine\Fleet\CombatEngine\Models\PlayerGroup;

class Battle
{
	private BattleReport $report;
	private bool $battleStarted;

	public function __construct(private PlayerGroup $attackers, private PlayerGroup $defenders, private int $rounds = 6)
	{
		$this->battleStarted = false;
		$this->report = new BattleReport();
	}

	public function startBattle(bool $debug = false)
	{
		if (!$debug) {
			ob_start();
		}

		$this->battleStarted = true;

		log_var('attackers', print_r($this->attackers, true));
		log_var('defenders', print_r($this->defenders, true));

		$round = new Round($this->attackers, $this->defenders, 0);
		$this->report->addRound($round);

		for ($i = 1; $i <= $this->rounds; $i++) {
			$att_lose = $this->attackers->isEmpty();
			$deff_lose = $this->defenders->isEmpty();

			if ($att_lose || $deff_lose) {
				$this->checkWhoWon($att_lose, $deff_lose);
				$this->report->setBattleResult($this->attackers->battleResult, $this->defenders->battleResult);

				if (!$debug) {
					ob_get_clean();
				}

				return;
			}

			$round = new Round($this->attackers, $this->defenders, $i);
			$round->startRound();

			$this->report->addRound($round);

			$this->attackers = $round->getAfterBattleAttackers();
			$this->defenders = $round->getAfterBattleDefenders();
		}

		$this->checkWhoWon($this->attackers->isEmpty(), $this->defenders->isEmpty());

		if (!$debug) {
			ob_get_clean();
		}
	}

	private function checkWhoWon(bool $att_lose, bool $deff_lose): void
	{
		if ($att_lose && !$deff_lose) {
			$this->attackers->battleResult = BattleResult::LOSE;
			$this->defenders->battleResult = BattleResult::WIN;
		} elseif (!$att_lose && $deff_lose) {
			$this->attackers->battleResult = BattleResult::WIN;
			$this->defenders->battleResult = BattleResult::LOSE;
		} else {
			$this->attackers->battleResult = BattleResult::DRAW;
			$this->defenders->battleResult = BattleResult::DRAW;
		}
	}

	public function getReport(bool $debug = false): BattleReport
	{
		if (!$this->battleStarted) {
			$this->startBattle($debug);
		}

		return $this->report;
	}
}
