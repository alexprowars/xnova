<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\request;
use Xcms\sql;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

class OfficierController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
		
		if (user::get()->data['urlaubs_modus_time'] > 0)
			$this->message("Нет доступа!");

		strings::includeLang('officier');
	}
	
	public function show ()
	{
		global $resource, $reslist;

		if (isset($_POST['buy']))
		{
			$need_c = 0;
			$times = 0;

			if (isset($_POST['week']) && $_POST['week'] != "")
			{
				$need_c = 20;
				$times = 604800;
			}
			elseif (isset($_POST['2week']) && $_POST['2week'] != "")
			{
				$need_c = 40;
				$times = 1209600;
			}
			elseif (isset($_POST['month']) && $_POST['month'] != "")
			{
				$need_c = 80;
				$times = 2592000;
			}
		
			if ($need_c > 0 && $times > 0 && user::get()->data['credits'] >= $need_c)
			{
				$selected = request::P('buy', 0, VALUE_INT);

				if (in_array($selected, $reslist['officier']))
				{
					if (user::get()->data[$resource[$selected]] > time())
						user::get()->data[$resource[$selected]] = user::get()->data[$resource[$selected]] + $times;
					else
						user::get()->data[$resource[$selected]] = time() + $times;

					user::get()->data['credits'] -= $need_c;

					sql::build()->update('game_users')->set(array
					(
						'credits' => user::get()->data['credits'],
						$resource[$selected] => user::get()->data[$resource[$selected]],
					))
					->where('id', '=', user::get()->getId())->execute();
		
					db::query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . user::get()->data['id'] . ", " . time() . ", " . ($need_c * (-1)) . ", 5)");
		
					$Message = _getText('OffiRecrute');
				}
				else
					$Message = "НУ ТЫ И ЧИТАК!!!!!!";
			}
			else
				$Message = _getText('NoPoints');
		
			$this->message($Message, _getText('Officier'), '?set=officier', 2);
		}
		else
		{
			$parse['off_points'] = _getText('off_points');
			$parse['alv_points'] = strings::pretty_number(user::get()->data['credits']);
			$parse['list'] = array();

			foreach ($reslist['officier'] AS $officier)
			{
				$bloc['off_id'] = $officier;
				$bloc['off_tx_lvl'] = _getText('ttle', $officier);

				if (user::get()->data[$resource[$officier]] > time())
				{
					$bloc['off_lvl'] = "<font color=\"#00ff00\">Нанят до : " . datezone("d.m.Y H:i", user::get()->data[$resource[$officier]]) . "</font>";
					$bloc['off_link'] = "<font color=\"red\">Продлить</font>";
				}
				else
				{
					$bloc['off_lvl'] = "<font color=\"#ff0000\">Не нанят</font>";
					$bloc['off_link'] = "<font color=\"red\">Нанять</font>";
				}

				$bloc['off_desc'] = _getText('Desc', $officier);
				$bloc['off_powr'] = _getText('power', $officier);

				$bloc['off_link'] .= "<br><br><input type=\"hidden\" name=\"buy\" value=\"" . $officier . "\"><input type=\"submit\" name=\"week\" value=\"на неделю\"><br>Стоимость:&nbsp;<font color=\"lime\">20</font>&nbsp;кр.<div class=\"separator\"></div><input type=\"submit\" name=\"2week\" value=\"на 2 недели\"><br>Стоимость:&nbsp;<font color=\"lime\">40</font>&nbsp;кр.<div class=\"separator\"></div><input type=\"submit\" name=\"month\" value=\"на месяц\"><br>Стоимость:&nbsp;<font color=\"lime\">80</font>&nbsp;кр.<div class=\"separator\"></div>";
				$parse['list'][] = $bloc;
			}
		
			$this->setTemplate('officier');
			$this->set('parse', $parse);
		}
		
		$this->setTitle('Офицеры');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>