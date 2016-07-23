<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Helpers;
use App\Lang;
use Xnova\Controller;

class OfficierController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		if ($this->user->vacation > 0)
			$this->message("Нет доступа!");

		Lang::includeLang('officier');
	}
	
	public function indexAction ()
	{
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
		
			if ($need_c > 0 && $times > 0 && $this->user->credits >= $need_c)
			{
				$selected = $this->request->getPost('buy', 'int', 0);

				if (in_array($selected, $this->storage->reslist['officier']))
				{
					if ($this->user->{$this->storage->resource[$selected]} > time())
						$this->user->{$this->storage->resource[$selected]} = $this->user->{$this->storage->resource[$selected]} + $times;
					else
						$this->user->{$this->storage->resource[$selected]} = time() + $times;

					$this->user->credits -= $need_c;
					$this->user->update();
		
					$this->db->query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . $this->user->id . ", " . time() . ", " . ($need_c * (-1)) . ", 5)");
		
					$Message = _getText('OffiRecrute');
				}
				else
					$Message = "НУ ТЫ И ЧИТАК!!!!!!";
			}
			else
				$Message = _getText('NoPoints');
		
			$this->message($Message, _getText('Officier'), '/officier/', 2);
		}
		else
		{
			$parse['off_points'] = _getText('off_points');
			$parse['alv_points'] = Helpers::pretty_number($this->user->credits);
			$parse['list'] = [];

			foreach ($this->storage->reslist['officier'] AS $officier)
			{
				$bloc['off_id'] = $officier;
				$bloc['off_tx_lvl'] = _getText('ttle', $officier);

				if ($this->user->{$this->storage->resource[$officier]} > time())
				{
					$bloc['off_lvl'] = "<font color=\"#00ff00\">Нанят до : " . $this->game->datezone("d.m.Y H:i", $this->user->{$this->storage->resource[$officier]}) . "</font>";
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

			$this->view->setVar('parse', $parse);
		}
		
		$this->tag->setTitle('Офицеры');
		$this->showTopPanel(false);
	}
}