<?php

namespace App\Controllers;

class LogsController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$this->view->pick('jurnal');
		
		$journal = @intval($_POST['journal']);
		$days = @intval($_POST['days']);
		$lim = @intval($_POST['lim']);
		$range = @intval($_POST['range']);
		
		$text_query = "";
		
		if ($journal < 1 || $journal > 15)
			$journal = 1;
		if ($days < 0 || $days > 1)
			$days = 0;
		
		$parse['type'] = "<option value=\"1\"" . (($journal == "1") ? " SELECTED" : "") . ">Атаковать</option>";
		$parse['type'] .= "<option value=\"2\"" . (($journal == "2") ? " SELECTED" : "") . ">Объединить</option>";
		$parse['type'] .= "<option value=\"3\"" . (($journal == "3") ? " SELECTED" : "") . ">Транспорт</option>";
		$parse['type'] .= "<option value=\"4\"" . (($journal == "4") ? " SELECTED" : "") . ">Оставить</option>";
		$parse['type'] .= "<option value=\"5\"" . (($journal == "5") ? " SELECTED" : "") . ">Удерживать</option>";
		$parse['type'] .= "<option value=\"6\"" . (($journal == "6") ? " SELECTED" : "") . ">Шпионаж</option>";
		$parse['type'] .= "<option value=\"7\"" . (($journal == "7") ? " SELECTED" : "") . ">Колонизировать</option>";
		$parse['type'] .= "<option value=\"8\"" . (($journal == "8") ? " SELECTED" : "") . ">Переработать</option>";
		$parse['type'] .= "<option value=\"9\"" . (($journal == "9") ? " SELECTED" : "") . ">Уничтожить</option>";
		$parse['type'] .= "<option value=\"10\"" . (($journal == "10") ? " SELECTED" : "") . ">Создать базу</option>";
		$parse['type'] .= "<option value=\"15\"" . (($journal == "15") ? " SELECTED" : "") . ">Экспедиция</option>";
		
		
		$parse['day'] = "<option value=\"0\"" . (($days == "0") ? " SELECTED" : "") . ">Сегодня</option>";
		$parse['day'] .= "<option value=\"1\"" . (($days == "1") ? " SELECTED" : "") . ">Вчера</option>";
		
		$night_time = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));
		
		switch ($days)
		{
			case 0:
				$text_query .= "AND `time` > " . $night_time;
				break;
			case 1:
				$text_query .= "AND (`time` > " . ($night_time - 86400) . " AND `time` < " . $night_time . ")";
				break;
			default:
				$text_query .= "AND `time` > " . $night_time;
				break;
		}
		
		$start = floor($range / 100 % 100) * 100;
		
		$MaxLogs = $this->db->query("SELECT COUNT(*) AS `count` FROM game_logs WHERE `s_id` = '".$this->user->id."' AND `mission` = '" . $journal . "' " . $text_query . ";")->fetch();
		
		$Logs = $this->db->query("SELECT * FROM game_logs WHERE `s_id` = '".$this->user->id."' AND `mission` = '" . $journal . "' " . $text_query . " ORDER BY `time` DESC LIMIT " . $start . ",10;");
		
		$count = floor($MaxLogs['count'] / 10);
		
		if ($Logs->numRows() > 0)
		{
		
			$parse['count'] = "<td colspan=\"1\" class=\"c\" width=\"10\"%><center>Игрок:</center></td>
		<td colspan=\"1\" class=\"c\" width=\"10\"%><center>
		<select name=\"lim\" onChange=\"document.forms[1].submit()\">";
		
			$parse['count'] .= "<option value=\"0\"" . (($lim == "0") ? " SELECTED" : "") . ">Я</option>";
			$parse['count'] .= "<option value=\"1\"" . (($lim == "1") ? " SELECTED" : "") . ">Враг</option>";
		
			$parse['count'] .= "</td>
		<td colspan=\"1\" class=\"c\" width=\"10\"%><center>Записи:</center></td>
		<td colspan=\"1\" class=\"c\" width=\"20\"%><center>
		<select name=\"start\" onChange=\"document.forms[1].submit()\">";
		
			for ($Page = 0; $Page <= $count; $Page++)
			{
				$PageValue = ($Page * $range) + 1;
				$PageRange = $PageValue + $range - 1;
				$parse['count'] .= "<option value=\"" . $PageValue . "\"" . (($range == $PageValue) ? " SELECTED" : "") . ">" . $PageValue . "-" . $PageRange . "</option>";
			}
		
			$parse['count'] .= "</td>";
		
			$parse['log'] .= "<br><table width=\"679\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\">";
			$parse['log'] .= "<tr><td class=\"c\">Время</td><td class=\"c\">Отправлен с</td><td class=\"c\">Отправлен на</td><td class=\"c\">&nbsp;</td></tr>";
		
			while ($Log = $Logs->fetch())
			{
		
				$parse['log'] .= "<tr><td class=\"b\"><center><b>" . $this->game->datezone("H:i:s", $Log['time']) . "</b></center></td>
						<td class=\"b\"><center><a href=\"/galaxy/?r=3&galaxy=" . $Log['s_galaxy'] . "&system=" . $Log['s_system'] . "\">[" . $Log['s_galaxy'] . ":" . $Log['s_system'] . ":" . $Log['s_planet'] . "]</a></center></td>
						<td class=\"b\"><center><font color=lime><a href=\"/galaxy/?r=3&galaxy=" . $Log['e_galaxy'] . "&system=" . $Log['e_system'] . "\">[" . $Log['e_galaxy'] . ":" . $Log['e_system'] . ":" . $Log['e_planet'] . "]</a>&nbsp;<a href=/players/?id=" . $Log['e_id'] . "></font></center></td>
						<td class=\"b\"><center><a href=/players/?id=" . $Log['e_id'] . "><img src=/assets/images/s.gif alt=\"Информация об игроке\" title=\"Информация об игроке\" border=0></a></td></center></tr>";
		
			}
		
			$parse['log'] .= "</table>";
		}
		else
		{
			$parse['count'] = "<td colspan=\"2\" class=\"c\" width=40%><center>нет записей</center></td>";
		}
		
		
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Бортовой журнал');
	}
}

?>