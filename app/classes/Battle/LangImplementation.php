<?php

use App\Battle\Utils\Lang;

class LangImplementation implements Lang
{
    public function getShipName($id)
    {
        return _getText('tech', $id);
    }

    public function getAttackersAttackingDescr($amount, $damage)
    {
        return 'Атакующий флот делает ' . $amount . " выстрела(ов) с общей мощностью ".$damage." по защитнику. ";
    }
    public function getDefendersDefendingDescr($damage)
    {
        return 'Щиты защитника поглощают ' . $damage . ' мощности.';
    }
    public function getDefendersAttackingDescr($amount, $damage)
    {
        return 'Защитный флот делает ' . $amount . " выстрела(ов) с общей мощностью ".$damage." по атакующему. ";
    }
    public function getAttackersDefendingDescr($damage)
    {
        return 'Щиты атакующего поглащают ' . $damage . ' мощности.';
    }
}

?>