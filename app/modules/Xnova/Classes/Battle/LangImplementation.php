<?php

namespace Xnova\Battle;

use Xnova\Battle\Utils\Lang;

class LangImplementation implements Lang
{
	public function getShipName ($id)
	{
		return _getText('tech', $id);
	}

	public function getAttackersAttackingDescr ($amount, $damage)
	{
		return 'Атакующий флот делает '.$amount." выстрела(ов) с общей мощностью ".$damage." по защитнику. ";
	}

	public function getDefendersDefendingDescr ($damage)
	{
		return 'Щиты защитника поглощают '.$damage.' мощности.';
	}

	public function getDefendersAttackingDescr ($amount, $damage)
	{
		return 'Защитный флот делает '.$amount." выстрела(ов) с общей мощностью ".$damage." по атакующему. ";
	}

	public function getAttackersDefendingDescr ($damage)
	{
		return 'Щиты атакующего поглащают '.$damage.' мощности.';
	}

	public function getAttackerHasWon ()
	{
	}

	public function getDefendersHasWon ()
	{
	}

	public function getDraw ()
	{
	}

	public function getStoleDescr ($metal, $crystal, $deuterium)
	{
	}

	public function getAttackersLostUnits ($units)
	{
	}

	public function getDefendersLostUnits ($units)
	{
	}

	public function getFloatingDebris ($metal, $crystal)
	{
	}

	public function getMoonProb ($prob)
	{
	}

	public function getNewMoon ()
	{
	}
}

?>