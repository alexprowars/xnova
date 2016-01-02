<?php
namespace App;

class Helpers
{
	static function strtolower ($text)
	{
		return mb_strtolower($text, 'UTF-8');
	}

	static function strtoupper ($text)
	{
		return mb_strtoupper($text, 'UTF-8');
	}

	static function getDateString ($type, $value)
	{
		$data = array();

		if ($type == 'month')
			$data = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
		elseif ($type == 'week')
			$data = array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота');

		return (isset($data[$value]) ? $data[$value] : '');
	}

	static function getTimeFromString ($str, $is_sec = false)
	{
		$str = explode(":", $str);

		if ($str[0] > 23)
			$str[0] = 23;
		if ($str[1] > 59)
			$str[1] = 59;

		$str = $str[0] * ($is_sec ? 3600 : 60) + $str[1];

		return $str;
	}

	static function translite($st)
	{
	 	$st = strtr($st,'абвгдезийклмнопрстуфх','abvgdezijklmnoprstufx');
	 	$st = strtr($st,'АБВГДЕЗИЙКЛМНОПРСТУФХ','ABVGDEZIJKLMNOPRSTUFX');

	 	$st = strtr($st,array(
			 'ё'=>'yo', 'ж'=>'zh', 'ц'=>'cz', 'ч'=>'ch', 'ш'=>'sh',
			 'щ'=>'shh', 'ъ'=>'``', 'ы'=>'y`', 'ь'=>'`', 'э'=>'e`', 'ю'=>'yu', 'я'=>'ya',
			 'Ё'=>'YO', 'Ж'=>'ZH', 'Ц'=>'CZ', 'Ч'=>'CH', 'Ш'=>'SH',
			 'Щ'=>'SHH', 'Ъ'=>'``', 'Ы'=>'Y`', 'Ь'=>'`', 'Э'=>'E`', 'Ю'=>'YU', 'Я'=>'YA'
			 )
	 	);

	 return $st;
	 }

	static function untranslite($st)
	{
		$st = strtr($st,array(
			'yo'=>'ё', 'zh'=>'ж', 'cz'=>'ц', 'ch'=>'ч', 'sh'=>'ш',
			'shh'=>'щ', '``'=>'ъ', 'y`'=>'ы', '`'=>'ь', 'e`'=>'э', 'yu'=>'ю', 'ya'=>'я',
			'YO'=>'Ё', 'ZH'=>'Ж', 'CZ'=>'Ц', 'CH'=>'Ч', 'SH'=>'Ш',
			'SHH'=>'Щ', 'Y`'=>'Ы', 'E`'=>'Э', 'YU'=>'Ю', 'YA'=>'Я'
			 )
		);

		$st = strtr($st,'abvgdezijklmnoprstufx', 'абвгдезийклмнопрстуфх');
		$st = strtr($st,'ABVGDEZIJKLMNOPRSTUFX', 'АБВГДЕЗИЙКЛМНОПРСТУФХ');

		return $st;
	 }

	static function CheckString ($str, $cut = false)
	{
		if ($cut)
			$str = strip_tags($str);

	   return htmlspecialchars($str);
	}

	static function morph ($value, $gender = '', $declension = '')
	{
	    if (preg_match('/1\d$/', $value))
	        $n = 2;
	    elseif (preg_match('/1$/', $value))
	        $n = 0;
	    elseif (preg_match('/(2|3|4)$/', $value))
	        $n = 1;
	    else
	        $n = 2;

	    if ($gender == 'masculine') {
	        $ends = array(1 => array('', 'а', 'ов'), 2 => array('ь', 'я', 'ей'), 3 => array('', 'а', ''), 4 => array('ся', 'ось', 'ось'));
	        return $ends[$declension][$n];
	    } elseif ($gender == 'feminine') {
	        $ends = array(1 => array('а', 'и', ''), 2 => array('я', 'и', 'й'), 3 => array('ья', 'ьи', 'ей'), 4 => array('ь', 'и', 'ей'), 5 => array('а', 'ы', ''));
	        return $ends[$declension][$n];
	    } elseif ($gender == 'neuter') {
	        $ends = array(1 => array('е', 'я', 'ей'), 2 => array('ое', 'ых', 'ых'));
			return $ends[$declension][$n];
	    } else {
	        return $n;
	    }
	}

	static function formatBytes ($size)
	{
	     $units = array(' B', ' KiB', ' MiB', ' GiB', ' TiB');

	     for ($i = 0; $size >= 1024 && $i < 4; $i++)
			 $size /= 1024;

	     return round($size, 2).$units[$i];
	}

	static function colorNumber ($n)
	{
		if ($n > 0)
			return '<span class="positive">' . $n . '</span>';
		elseif ($n < 0)
			return '<span class="negative">' . $n . '</span>';
		else
			return $n;
	}

	static function pretty_number ($n)
	{
		if ($n > 1000000000)
			return number_format(floor($n / 1000000), 0, ",", ".").'kk';

		return number_format($n, 0, ",", ".");
	}

	static function pretty_phone ($phone)
	{
		$phone = Socials::phoneFormat($phone);

		if ($phone != '')
		{
			$phone = preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "8 ($2) $3-$4-$5", $phone);
		}

		return $phone;
	}

	static function pretty_time ($seconds, $separator = '')
	{
		$day    = floor($seconds / (24 * 3600));
		$hh     = floor($seconds / 3600 % 24);
		$mm     = floor($seconds / 60 % 60);
		$ss     = floor($seconds / 1 % 60);

		$time = '';

		if ($day != 0)
			$time .= $day.(($separator != '') ? $separator : ' д. ');

		if ($hh > 0)
			$time .= $hh.(($separator != '') ? $separator : ' ч. ');

		if ($mm > 0)
			$time .= $mm.(($separator != '') ? $separator : ' мин. ');

		if ($ss != 0)
			$time .= $ss.(($separator != '') ? '' : ' с. ');

		if (!$time)
			$time = '-';

		return $time;
	}

	static function is_email($email)
	{
		return (preg_match('#^[^\\x00-\\x1f@]+@[^\\x00-\\x1f@]{2,}\.[a-z]{2,}$#iu', $email) != 0);
	}

	static function FormatText ($text)
	{
		$text = htmlspecialchars(str_replace("'", '&#39;', $text));
		$text = addslashes($text);
		$text = trim ( nl2br ( strip_tags ( $text, '<br>' ) ) );
		$text = str_replace(array("\r\n", "\n", "\r"), '', $text);

		return $text;
	}

	static function cutString($string, $maxlen)
	{
		$len = (mb_strlen($string) > $maxlen) ? mb_strripos(mb_substr($string, 0, $maxlen), ' ') : $maxlen;

		$cutStr = mb_substr($string, 0, $len);

		return (mb_strlen($string) > $maxlen) ? '' . $cutStr . '...' : '' . $cutStr . '';
	}

	static function randomSequence ($dictionary = '', $length = 6)
	{
		if ($dictionary == '')
			$dictionary = 'aazertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN1234567890';

		$count 		= mb_strlen($dictionary, 'UTF-8');
		$sequence 	= '';

		for ($i = 0; $i < $length; $i++)
		{
			$sequence .= substr($dictionary, rand(0, $count - 1), 1);
		}

		return $sequence;
	}

	static function pagination ($count, $per_page, $link, $page = 0)
	{
		if (!is_numeric($page))
			return '';

		$pages_count = @ceil($count / $per_page);

		if ($page == 0 || $page > $pages_count)
			$page = 1;

		$pages = '<ul class="pagination pagination-sm">';
		$end = 0;

		if ($pages_count > 1)
		{
			for ($i = 1; $i <= $pages_count; $i++)
			{
				if (($page <= $i + 3 && $page >= $i - 3) || $i == 1 || $i == $pages_count || $pages_count <= 6)
				{
					$end = 0;

					if ($i == $page)
						$pages .= "<li class=\"active\"><a href=\"" . $link . "&p=" . $i . "\">" . $i . "</a></li>";
					else
						$pages .= "<li><a href=\"" . $link . "&p=" . $i . "\">" . $i . "</a></li>";
				}
				else
				{
					if ($end == 0)
						$pages .= '<li><a href="javascript:;">... | </a></li>';

					$end = 1;
				}
			}
		}
		else
			$pages .= '<li><a href="javascript:;">1</a></li>';

		$pages .= '</ul>';

		return $pages;
	}

	static function GetStartAdressLink ($FleetRow, $FleetType = '')
	{
		$Link = "<a href=\"/galaxy/?r=3&amp;galaxy=" . $FleetRow['fleet_start_galaxy'] . "&amp;system=" . $FleetRow['fleet_start_system'] . "\" " . $FleetType . " >";
		$Link .= "[" . $FleetRow['fleet_start_galaxy'] . ":" . $FleetRow['fleet_start_system'] . ":" . $FleetRow['fleet_start_planet'] . "]</a>";
		return $Link;
	}

	static function GetTargetAdressLink ($FleetRow, $FleetType = '')
	{
		$Link = "<a href=\"/galaxy/?r=3&amp;galaxy=" . $FleetRow['fleet_end_galaxy'] . "&amp;system=" . $FleetRow['fleet_end_system'] . "\" " . $FleetType . " >";
		$Link .= "[" . $FleetRow['fleet_end_galaxy'] . ":" . $FleetRow['fleet_end_system'] . ":" . $FleetRow['fleet_end_planet'] . "]</a>";
		return $Link;
	}

	static function BuildPlanetAdressLink ($CurrentPlanet)
	{
		$Link = "<a href=\"/galaxy/?r=3&amp;galaxy=" . $CurrentPlanet['galaxy'] . "&amp;system=" . $CurrentPlanet['system'] . "\">";
		$Link .= "[" . $CurrentPlanet['galaxy'] . ":" . $CurrentPlanet['system'] . ":" . $CurrentPlanet['planet'] . "]</a>";
		return $Link;
	}

	static function BuildHostileFleetPlayerLink ($FleetRow)
	{
		$Link = $FleetRow['username'] . " ";
		$Link .= "<a href=\"?set=messages&amp;mode=write&amp;id=" . $FleetRow['fleet_owner'] . "\" title=\"" . _getText('ov_message') . "\"><span class='sprite skin_m'></span></a>";

		return $Link;
	}

	static function InsertJavaScriptChronoApplet ($Type, $Ref, $Value)
	{
		return "<script>FlotenTime('bxx" . $Type . $Ref . "', " . $Value . ");</script>";
	}

	static function allowMobileVersion ()
	{
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);

		$result = true;

		if (!isMobile())
			$result = false;

		if (strpos($ua, 'webkit/5') === false)
			$result = false;

		if (strpos($ua, 'android 2') !== false || strpos($ua, 'android 3') !== false)
			$result = false;

		return $result;
	}
}

?>