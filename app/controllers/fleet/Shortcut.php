<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2013 XNova Game Group
 * @var $user user
 * ICQ: 8696096, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xcms\db;
use Xcms\request;
use Xcms\sql;
use Xnova\User;

if (!defined("INSIDE"))
	die("attemp hacking");

$mode = request::G('mode');
$a = request::G('a');

$html = '';

$inf = $this->db->query("SELECT fleet_shortcut FROM game_users_info WHERE id = " . $this->user->data['id'] . ";", true);

if (isset($_GET['mode']))
{
	if ($_POST)
	{
		if ($_POST["n"] == "" || !preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $_POST["n"]))
			$_POST["n"] = "Планета";

		$g = intval($_POST['g']);
		$s = intval($_POST['s']);
		$i = intval($_POST['p']);
		$t = intval($_POST['t']);

		if ($g < 1 || $g > MAX_GALAXY_IN_WORLD)
			$g = 1;
		if ($s < 1 || $s > MAX_SYSTEM_IN_GALAXY)
			$s = 1;
		if ($i < 1 || $i > MAX_PLANET_IN_SYSTEM)
			$i = 1;
		if ($t != 1 && $t != 2 && $t != 3 && $t != 5)
			$t = 1;

		$inf['fleet_shortcut'] .= strip_tags(str_replace(',', '', $_POST['n'])) . "," . $g . "," . $s . "," . $i . "," . $t . "\r\n";

		sql::build()->update('game_users_info')->setField('fleet_shortcut', $inf['fleet_shortcut'])->where('id', '=', $this->user->getId())->execute();

		if (isset($_SESSION['fleet_shortcut']))
			unset($_SESSION['fleet_shortcut']);

		$this->message("Ссылка на планету добавлена!", "Добавление ссылки", "?set=fleet&page=shortcut");
	}

	$g = request::G('g', 1, VALUE_INT);
	$s = request::G('s', 1, VALUE_INT);
	$i = request::G('i', 1, VALUE_INT);
	$t = request::G('t', 1, VALUE_INT);

	if ($g < 1 || $g > MAX_GALAXY_IN_WORLD)
		$g = 1;
	if ($s < 1 || $s > MAX_SYSTEM_IN_GALAXY)
		$s = 1;
	if ($i < 1 || $i > MAX_PLANET_IN_SYSTEM)
		$i = 1;
	if ($t != 1 && $t != 2 && $t != 3 && $t != 5)
		$t = 1;

	$this->setTemplate('fleet/shortcut_new');
	$this->set('g', $g);
	$this->set('s', $s);
	$this->set('i', $i);
	$this->set('t', $t);
}
elseif (isset($_GET['a']))
{
	if ($_POST)
	{
		$a = intval($_POST['a']);
		$scarray = explode("\r\n", $inf['fleet_shortcut']);

		if (isset($_POST["delete"]))
		{
			unset($scarray[$a]);
			$inf['fleet_shortcut'] = implode("\r\n", $scarray);

			sql::build()->update('game_users_info')->setField('fleet_shortcut', $inf['fleet_shortcut'])->where('id', '=', $this->user->getId())->execute();

			if (isset($_SESSION['fleet_shortcut']))
				unset($_SESSION['fleet_shortcut']);

			$this->message("Ссылка была успешно удалена!", "Удаление ссылки", "?set=fleet&page=shortcut");
		}
		else
		{
			$r = explode(",", $scarray[$a]);

			$_POST['n'] = str_replace(',', '', $_POST['n']);

			$r[0] = strip_tags($_POST['n']);
			$r[1] = intval($_POST['g']);
			$r[2] = intval($_POST['s']);
			$r[3] = intval($_POST['p']);
			$r[4] = intval($_POST['t']);

			if ($r[1] < 1 || $r[1] > MAX_GALAXY_IN_WORLD)
				$r[1] = 1;
			if ($r[2] < 1 || $r[2] > MAX_SYSTEM_IN_GALAXY)
				$r[2] = 1;
			if ($r[3] < 1 || $r[3] > MAX_PLANET_IN_SYSTEM)
				$r[3] = 1;
			if ($r[4] != 1 && $r[4] != 2 && $r[4] != 3 && $r[4] != 5)
				$r[4] = 1;

			$scarray[$a] = implode(",", $r);
			$inf['fleet_shortcut'] = implode("\r\n", $scarray);

			sql::build()->update('game_users_info')->setField('fleet_shortcut', $inf['fleet_shortcut'])->where('id', '=', $this->user->getId())->execute();

			if (isset($_SESSION['fleet_shortcut']))
				unset($_SESSION['fleet_shortcut']);

			$this->message("Ссылка была обновлена!", "Обновление ссылки", "?set=fleet&page=shortcut");
		}
	}

	if ($inf['fleet_shortcut'])
	{
		$a = request::G('a', 0, VALUE_INT);
		$scarray = explode("\r\n", $inf['fleet_shortcut']);

		if (isset($scarray[$a]))
		{
			$c = explode(',', $scarray[$a]);

			$this->setTemplate('fleet/shortcut_edit');
			$this->set('c', $c);
			$this->set('a', $a);
		}
		else
			$this->message("Данной ссылки не существует!", "Ссылки", "?set=fleet&page=shortcut");
	}
	else
		$this->message("Ваш список быстрых ссылок пуст!", "Ссылки", "?set=fleet&page=shortcut");
}
else
{

	$html = '<table class="table">
	<tr height="20">
	<td colspan="2" class="c">Ссылки (<a href="?set=fleet&page=shortcut&mode=add">Добавить</a>)</td>
	</tr>';

	if ($inf['fleet_shortcut'])
	{

		$scarray = explode("\r\n", $inf['fleet_shortcut']);
		$i = $e = 0;
		foreach ($scarray as $a => $b)
		{
			if ($b != "")
			{
				$c = explode(',', $b);
				if ($i == 0)
				{
					$html .= "<tr height=\"20\">";
				}
				$html .= "<th width=50%><a href=\"?set=fleet&page=shortcut&a=" . $e++ . "\">";
				$html .= "{$c[0]} {$c[1]}:{$c[2]}:{$c[3]}";
				if ($c[4] == 2)
				{
					$html .= " (E)";
				}
				elseif ($c[4] == 3)
				{
					$html .= " (L)";
				}
				elseif ($c[4] == 5)
				{
					$html .= " (B)";
				}
				$html .= "</a></th>";
				if ($i == 1)
				{
					$html .= "</tr>";
				}
				if ($i == 1)
				{
					$i = 0;
				}
				else
				{
					$i = 1;
				}
			}

		}
		if ($i == 1)
		{
			$html .= "<th>&nbsp;</th></tr>";
		}

	}
	else
	{
		$html .= "<th colspan=\"2\">Список ссылок пуст</th>";
	}

	$html .= '<tr><td colspan=2 class=c><a href=?set=fleet>Назад</a></td></tr></tr></table>';
}

$this->setTitle("Закладки");
$this->setContent($html);
$this->display();

?>