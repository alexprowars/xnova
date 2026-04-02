<?php

namespace App\Engine\Fleet\CombatEngine\CombatObject;

use App\Engine\Fleet\CombatEngine\Exception;
use App\Engine\Fleet\CombatEngine\Models\ShipType;

class ShipsCleaner
{
	private ShipType $shipType;
	private int $lastShipHit;
	private int $lastShots;

	private int $exploded;
	private float $remainLife;

	public function __construct(ShipType $shipType, int $lastShipHit, int $lastShots)
	{
		if ($lastShipHit < 0) {
			throw new Exception('Negative $lastShipHit');
		}
		if ($lastShots < 0) {
			throw new Exception('Negative $lastShots');
		}

		$this->shipType = $shipType->cloneMe();
		$this->lastShipHit = $lastShipHit;
		$this->lastShots = $lastShots;
	}

	/**
	 * Start the system
	 */
	public function start(): void
	{
		$prob = 1 - $this->shipType->getCurrentLife() / ($this->shipType->getHull() * $this->shipType->getCount());

		if ($prob < 0 && $prob > -config('battle.EPSILON')) {
			$prob = 0;
		}

		if ($prob < 0) {
			throw new Exception("Negative prob");
		}

		if (config('battle.USE_BIEXPLOSION_SYSTEM') && $this->lastShipHit >= ($this->shipType->getCount() / config('battle.PROB_TO_REAL_MAGIC'))) {
			log_comment('lastShipHit bigger than getCount()/magic');

			if ($prob < config('battle.MIN_PROB_TO_EXPLODE')) {
				$probToExplode = 0;
			} else {
				$probToExplode = $prob;
			}
		} else {
			log_comment('lastShipHit smaller than getCount()/magic');
			$probToExplode = $prob * (1 - config('battle.MIN_PROB_TO_EXPLODE'));
		}

		/*** calculating the amount of exploded ships ***/
		$teoricExploded = round($this->shipType->getCount() * $probToExplode);

		if (config('battle.USE_EXPLODED_LIMITATION')) {
			$teoricExploded = min($teoricExploded, $this->lastShots);
		}

		$this->exploded = $teoricExploded; //bounded by the total shots fired to simulate a real combat :)

		/*** calculating the life of destroyed ships ***/

		//$this->remainLife = $this->exploded * (1 - $prob) * ($this->fighters->getCurrentLife() / $this->fighters->getCount());
		$this->remainLife = $this->shipType->getCurrentLife() / $this->shipType->getCount();
		log_var('prob', $prob);
		log_var('probToExplode', $probToExplode);
		log_var('teoricExploded', $teoricExploded);
		log_var('exploded', $this->exploded);
		log_var('remainLife', $this->remainLife);
	}

	/**
	 * Return the number of exploded ships
	 */
	public function getExplodedShips(): int
	{
		return $this->exploded;
	}

	/**
	 * Return the life of exploded ships
	 */
	public function getRemainLife(): float
	{
		return $this->remainLife;
	}
}
