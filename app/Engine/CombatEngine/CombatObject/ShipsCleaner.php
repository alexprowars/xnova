<?php

namespace App\Engine\CombatEngine\CombatObject;

use App\Engine\CombatEngine\Models\ShipType;
use Exception;

class ShipsCleaner
{
	private $lastShipHit;
	private $lastShots;

	private $exploded;
	private $remainLife;

	/**
	 * ShipsCleaner::__construct()
	 *
	 * @param mixed $shipType
	 * @param int $lastShipHit
	 * @param int $lastShots
	 * @throws Exception
	 */
	public function __construct(ShipType $shipType, $lastShipHit, $lastShots)
	{
		if ($lastShipHit < 0) {
			throw new Exception('Negative $lastShipHit');
		}
		if ($lastShots < 0) {
			throw new Exception('Negative $lastShots');
		}

		$this->fighters = $shipType->cloneMe();
		$this->lastShipHit = $lastShipHit;
		$this->lastShots = $lastShots;
	}

	/**
	 * ShipsCleaner::start()
	 * Start the system
	 * @throws Exception
	 */
	public function start()
	{
		$prob = 1 - $this->fighters->getCurrentLife() / ($this->fighters->getHull() * $this->fighters->getCount());

		if ($prob < 0 && $prob > -EPSILON) {
			$prob = 0;
		}

		if ($prob < 0) {
			throw new Exception("Negative prob");
		}

		if (USE_BIEXPLOSION_SYSTEM && $this->lastShipHit >= $this->fighters->getCount() / PROB_TO_REAL_MAGIC) {
			\log_comment('lastShipHit bigger than getCount()/magic');
			if ($prob < MIN_PROB_TO_EXPLODE) {
				$probToExplode = 0;
			} else {
				$probToExplode = $prob;
			}
		} else {
			\log_comment('lastShipHit smaller than getCount()/magic');
			$probToExplode = $prob * (1 - MIN_PROB_TO_EXPLODE);
		}

		/*** calculating the amount of exploded ships ***/
		$teoricExploded = round($this->fighters->getCount() * $probToExplode);

		if (USE_EXPLODED_LIMITATION) {
			$teoricExploded = min($teoricExploded, $this->lastShots);
		}

		$this->exploded = $teoricExploded; //bounded by the total shots fired to simulate a real combat :)

		/*** calculating the life of destroyed ships ***/

		//$this->remainLife = $this->exploded * (1 - $prob) * ($this->fighters->getCurrentLife() / $this->fighters->getCount());
		$this->remainLife = $this->fighters->getCurrentLife() / $this->fighters->getCount();
		\log_var('prob', $prob);
		\log_var('probToExplode', $probToExplode);
		\log_var('teoricExploded', $teoricExploded);
		\log_var('exploded', $this->exploded);
		\log_var('remainLife', $this->remainLife);
	}

	/**
	 * ShipsCleaner::getExplodeShips()
	 * Return the number of exploded ships
	 * @return int
	 */
	public function getExplodedShips()
	{
		return $this->exploded;
	}

	/**
	 * ShipsCleaner::getRemainLife()
	 * Return the life of exploded ships
	 * @return float
	 */
	public function getRemainLife()
	{
		return $this->remainLife;
	}
}
