<?php

namespace Xnova\Battle\Core;

use Xnova\Battle\Models\PlayerGroup;

class Battle
{
	private $attackers;
	private $defenders;
	private $report;
	private $battleStarted;
	private $rounds = 6;

	/**
	 * Battle::__construct()
	 *
	 * @param PlayerGroup $attackers
	 * @param PlayerGroup $defenders
	 * @param int $rounds
	 */
	public function __construct(PlayerGroup $attackers, PlayerGroup $defenders, $rounds = 6)
	{
		$this->attackers = $attackers;
		$this->defenders = $defenders;
		$this->battleStarted = false;
		$this->report = new BattleReport();
		$this->rounds = $rounds;
	}

	/**
	 * Battle::startBattle()
	 *
	 * @param bool $debug
	 * @return bool
	 * @throws \Exception
	 */
	public function startBattle($debug = false)
	{
		if (!$debug)
			ob_start();

		$this->battleStarted = true;

		\log_var('attackers', $this->attackers);
		\log_var('defenders', $this->defenders);

		$round = new Round($this->attackers, $this->defenders, 0);
		$this->report->addRound($round);

		for ($i = 1; $i <= $this->rounds; $i++)
		{
			$att_lose = $this->attackers->isEmpty();
			$deff_lose = $this->defenders->isEmpty();

			if ($att_lose || $deff_lose)
			{
				$this->checkWhoWon($att_lose, $deff_lose);
				$this->report->setBattleResult($this->attackers->battleResult, $this->defenders->battleResult);

				if (!$debug)
					ob_get_clean();

				return false;
			}

			$round = new Round($this->attackers, $this->defenders, $i);
			$round->startRound();

			$this->report->addRound($round);

			$this->attackers = $round->getAfterBattleAttackers();
			$this->defenders = $round->getAfterBattleDefenders();
		}

		$this->checkWhoWon($this->attackers->isEmpty(), $this->defenders->isEmpty());

		if (!$debug)
			ob_get_clean();

		return true;
	}

	/**
	 * Battle::checkWhoWon()
	 * Assign to groups the status win,lose or draw
	 * @param boolean $att_lose
	 * @param boolean $deff_lose
	 */
	private function checkWhoWon($att_lose, $deff_lose)
	{
		if ($att_lose && !$deff_lose)
		{
			$this->attackers->battleResult = BATTLE_LOSE;
			$this->defenders->battleResult = BATTLE_WIN;
		}
		elseif (!$att_lose && $deff_lose)
		{
			$this->attackers->battleResult = BATTLE_WIN;
			$this->defenders->battleResult = BATTLE_LOSE;
		}
		else
		{
			$this->attackers->battleResult = BATTLE_DRAW;
			$this->defenders->battleResult = BATTLE_DRAW;
		}
	}

	/**
	 * Battle::getReport()
	 * Start the battle if not and return the report.
	 * @param bool $debug
	 * @return BattleReport
	 * @throws \Exception
	 */
	public function getReport($debug = false)
	{
		if (!$this->battleStarted)
			$this->startBattle($debug);

		return $this->report;
	}
}
