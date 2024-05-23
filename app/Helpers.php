<?php

namespace App;

use Illuminate\Support\Facades\URL;

class Helpers
{
	public static function getDateString($type, $value)
	{
		$data = [];

		if ($type == 'month') {
			$data = ['', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
		} elseif ($type == 'week') {
			$data = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
		}

		return (isset($data[$value]) ? $data[$value] : '');
	}

	public static function getTimeFromString($str, $is_sec = false)
	{
		$str = explode(":", $str);

		if ($str[0] > 23) {
			$str[0] = 23;
		}
		if ($str[1] > 59) {
			$str[1] = 59;
		}

		$str = $str[0] * ($is_sec ? 3600 : 60) + $str[1];

		return $str;
	}

	public static function translite($st)
	{
		$st = strtr($st, 'абвгдезийклмнопрстуфх', 'abvgdezijklmnoprstufx');
		$st = strtr($st, 'АБВГДЕЗИЙКЛМНОПРСТУФХ', 'ABVGDEZIJKLMNOPRSTUFX');
		$st = strtr($st, ['ё' => 'yo', 'ж' => 'zh', 'ц' => 'cz', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '``', 'ы' => 'y`', 'ь' => '`', 'э' => 'e`', 'ю' => 'yu', 'я' => 'ya', 'Ё' => 'YO', 'Ж' => 'ZH', 'Ц' => 'CZ', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '``', 'Ы' => 'Y`', 'Ь' => '`', 'Э' => 'E`', 'Ю' => 'YU', 'Я' => 'YA']);

		return $st;
	}

	public static function untranslite($st)
	{
		$st = strtr($st, ['yo' => 'ё', 'zh' => 'ж', 'cz' => 'ц', 'ch' => 'ч', 'sh' => 'ш', 'shh' => 'щ', '``' => 'ъ', 'y`' => 'ы', '`' => 'ь', 'e`' => 'э', 'yu' => 'ю', 'ya' => 'я', 'YO' => 'Ё', 'ZH' => 'Ж', 'CZ' => 'Ц', 'CH' => 'Ч', 'SH' => 'Ш', 'SHH' => 'Щ', 'Y`' => 'Ы', 'E`' => 'Э', 'YU' => 'Ю', 'YA' => 'Я']);
		$st = strtr($st, 'abvgdezijklmnoprstufx', 'абвгдезийклмнопрстуфх');
		$st = strtr($st, 'ABVGDEZIJKLMNOPRSTUFX', 'АБВГДЕЗИЙКЛМНОПРСТУФХ');

		return $st;
	}

	public static function checkString($str, $cut = false)
	{
		if ($cut) {
			$str = strip_tags($str);
		}

		return htmlspecialchars($str);
	}

	public static function morph($value, $gender = '', $declension = '')
	{
		if (preg_match('/1\d$/', $value)) {
			$n = 2;
		} elseif (preg_match('/1$/', $value)) {
			$n = 0;
		} elseif (preg_match('/([234])$/', $value)) {
			$n = 1;
		} else {
			$n = 2;
		}

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

	public static function colorNumber($n)
	{
		if ($n > 0) {
			return '<span class="positive">' . $n . '</span>';
		} elseif ($n < 0) {
			return '<span class="negative">' . $n . '</span>';
		} else {
			return $n;
		}
	}

	public static function is_email($email)
	{
		return !!filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function cutString($string, $maxlen)
	{
		$len = (mb_strlen($string) > $maxlen) ? mb_strripos(mb_substr($string, 0, $maxlen), ' ') : $maxlen;

		$cutStr = mb_substr($string, 0, $len);

		return (mb_strlen($string) > $maxlen) ? '' . $cutStr . '...' : '' . $cutStr . '';
	}

	public static function pagination($count, $per_page, $link, $page = 0)
	{
		if (!is_numeric($page)) {
			return '';
		}

		$pages_count = @ceil($count / $per_page);

		if ($page == 0 || $page > $pages_count) {
			$page = 1;
		}

		$pages = '<div class="pagination pagination-sm">';
		$end = 0;

		if ($pages_count > 1) {
			for ($i = 1; $i <= $pages_count; $i++) {
				if (($page <= $i + 3 && $page >= $i - 3) || $i == 1 || $i == $pages_count || $pages_count <= 6) {
					$end = 0;

					if ($i == $page) {
						$pages .= '<div class="page-item active"><a href="' . $link . (strpos($link, '?') === false ? '?' : '&') . 'p=' . $i . '" class="page-link">' . $i . '</a></div>';
					} else {
						$pages .= '<div class="page-item"><a href="' . $link . (strpos($link, '?') === false ? '?' : '&') . 'p=' . $i . '" class="page-link">' . $i . '</a></div>';
					}
				} else {
					if ($end == 0) {
						$pages .= '<div class="page-item"><a href="javascript:;" class="page-link">... | </a></div>';
					}

					$end = 1;
				}
			}
		} else {
			$pages .= '<div class="page-item"><a href="javascript:;" class="page-link">1</a></div>';
		}

		$pages .= '</div>';

		return $pages;
	}

	public static function BuildPlanetAdressLink($CurrentPlanet)
	{
		$uri = URL::to('galaxy/' . $CurrentPlanet['galaxy'] . '/' . $CurrentPlanet['system'] . '/');

		return '<a href="' . $uri . '">[' . $CurrentPlanet['galaxy'] . ':' . $CurrentPlanet['system'] . ':' . $CurrentPlanet['planet'] . ']</a>';
	}

	public static function BuildHostileFleetPlayerLink($FleetRow)
	{
		$uri = URL::to('messages/write/' . $FleetRow->user_id . '/');

		return $FleetRow->user?->username . ' <a href="' . $uri . '" title="' . __('overview.ov_message') . '"><span class=\'sprite skin_m\'></span></a>';
	}

	public static function phoneFormat($phone)
	{
		if ($phone == '') {
			return '';
		}

		$phone = str_replace(['+', '-', '(', ')', ' '], '', $phone);

		if ($phone[0] == '8') {
			$phone[0] = '7';
		}

		if ($phone[0] != '7') {
			$phone = '7' . $phone;
		}

		if (mb_strlen($phone, 'UTF-8') == 11) {
			return $phone;
		} else {
			return '';
		}
	}

	public static function allowMobileVersion()
	{
		if (!isset($_SERVER['HTTP_USER_AGENT'])) {
			return false;
		}

		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);

		$result = true;

		if (!self::isMobile()) {
			$result = false;
		}

		if (strpos($ua, 'webkit/5') === false && strpos($ua, 'webkit/6') === false) {
			$result = false;
		}

		if (strpos($ua, 'android 2') !== false || strpos($ua, 'android 3') !== false) {
			$result = false;
		}

		return $result;
	}

	public static function isMobile(): bool
	{
		if (!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT'])) {
			return false;
		}

		return preg_match('/\b(?:a(?:ndroid|vantgo)|b(?:lackberry|olt|o?ost)|cricket|docomo|hiptop|i(?:emobile|p[ao]d)|kitkat|m(?:ini|obi)|palm|(?:i|smart|windows )phone|symbian|up\.(?:browser|link)|tablet(?: browser| pc)|(?:hp-|rim |sony )tablet|w(?:ebos|indows ce|os))/i', $_SERVER['HTTP_USER_AGENT']);
	}

	public static function convertIp($ip)
	{
		if (!is_numeric($ip)) {
			return sprintf("%u", ip2long($ip));
		} else {
			return long2ip($ip);
		}
	}
}
