<?php

namespace Xnova\Battle\Utils;

abstract class GeometricDistribution
{
	/**
	 * GeometricDistribution::getProbabilityFromMean()
	 *
	 * @param int $m: the mean
	 * @return int
	 */
	public static function getProbabilityFromMean($m)
	{
		if ($m <= 1 )
		{
			return 1;
		}
		return 1 / $m;
	}

	/**
	 * GeometricDistribution::getMeanFromProbability()
	 *
	 * @param int $p: the probability
	 * @return int
	 */
	public static function getMeanFromProbability($p)
	{
		if ($p == 0)
		{
			return INF;
		}
		return 1 / $p;
	}

	/**
	 * GeometricDistribution::getVarianceFromProbability()
	 *
	 * @param int $p: the probability
	 * @return int
	 */
	public static function getVarianceFromProbability($p)
	{
		if ($p == 0)
		{
			return INF;
		}
		return (1 - $p) / ($p * $p);
	}

	/**
	 * GeometricDistribution::getStandardDeviationFromProbability()
	 *
	 * @param int $p: the probability
	 * @return int
	 */
	public static function getStandardDeviationFromProbability($p)
	{
		return sqrt(self::getVarianceFromProbability($p));
	}

}

?>