<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Format;
use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Vars;

/**
 * @RoutePrefix("/officier")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class OfficierController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		if ($this->user->vacation > 0)
			throw new ErrorException("Нет доступа!");

		Lang::includeLang('officier', 'xnova');
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

				if (Vars::getItemType($selected) == Vars::ITEM_TYPE_OFFICIER)
				{
					if ($this->user->{Vars::getName($selected)} > time())
						$this->user->{Vars::getName($selected)} = $this->user->{Vars::getName($selected)} + $times;
					else
						$this->user->{Vars::getName($selected)} = time() + $times;

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
		
			throw new RedirectException($Message, _getText('Officier'), '/officier/', 2);
		}
		else
		{
			$parse['off_points'] = _getText('off_points');
			$parse['alv_points'] = Format::number($this->user->credits);
			$parse['list'] = [];

			foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $officier)
			{
				$bloc['off_id'] = $officier;
				$bloc['off_tx_lvl'] = _getText('ttle', $officier);

				if ($this->user->{Vars::getName($officier)} > time())
				{
					$bloc['off_lvl'] = "<font color=\"#00ff00\">Нанят до : " . $this->game->datezone("d.m.Y H:i", $this->user->{Vars::getName($officier)}) . "</font>";
					$bloc['off_link'] = "<font color=\"red\">Продлить</font>";
				}
				else
				{
					$bloc['off_lvl'] = "<font color=\"#ff0000\">Не нанят</font>";
					$bloc['off_link'] = "<font color=\"red\">Нанять</font>";
				}

				$bloc['off_desc'] = _getText('Desc', $officier);
				$bloc['off_powr'] = _getText('power', $officier);

				$bloc['off_link'] .= "<br><input type=\"hidden\" name=\"buy\" value=\"" . $officier . "\"><input type=\"submit\" name=\"week\" value=\"на неделю\"><br>Стоимость:&nbsp;<font color=\"lime\">20</font>&nbsp;кр.<div class=\"separator\"></div><input type=\"submit\" name=\"2week\" value=\"на 2 недели\"><br>Стоимость:&nbsp;<font color=\"lime\">40</font>&nbsp;кр.<div class=\"separator\"></div><input type=\"submit\" name=\"month\" value=\"на месяц\"><br>Стоимость:&nbsp;<font color=\"lime\">80</font>&nbsp;кр.<div class=\"separator\"></div>";
				$parse['list'][] = $bloc;
			}

			$this->view->setVar('parse', $parse);
		}
		
		$this->tag->setTitle('Офицеры');
		$this->showTopPanel(false);
	}
}