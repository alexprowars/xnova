<?php

namespace App;

use Illuminate\Support\Facades\URL;

class Helpers
{
	public static function translite($st)
	{
		$st = strtr($st, 'абвгдезийклмнопрстуфх', 'abvgdezijklmnoprstufx');
		$st = strtr($st, 'АБВГДЕЗИЙКЛМНОПРСТУФХ', 'ABVGDEZIJKLMNOPRSTUFX');
		$st = strtr($st, ['ё' => 'yo', 'ж' => 'zh', 'ц' => 'cz', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '``', 'ы' => 'y`', 'ь' => '`', 'э' => 'e`', 'ю' => 'yu', 'я' => 'ya', 'Ё' => 'YO', 'Ж' => 'ZH', 'Ц' => 'CZ', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '``', 'Ы' => 'Y`', 'Ь' => '`', 'Э' => 'E`', 'Ю' => 'YU', 'Я' => 'YA']);

		return $st;
	}

	public static function checkString($str, $cut = false)
	{
		if ($cut) {
			$str = strip_tags($str);
		}

		return htmlspecialchars($str);
	}

	public static function is_email($email)
	{
		return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
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

	public static function buildPlanetAdressLink($CurrentPlanet)
	{
		$uri = URL::to('galaxy/' . $CurrentPlanet['galaxy'] . '/' . $CurrentPlanet['system']);

		return '<a href="' . $uri . '">[' . $CurrentPlanet['galaxy'] . ':' . $CurrentPlanet['system'] . ':' . $CurrentPlanet['planet'] . ']</a>';
	}

	public static function buildHostileFleetPlayerLink($FleetRow)
	{
		$uri = URL::to('messages/write/' . $FleetRow->user_id);

		return $FleetRow->user?->username . ' <a href="' . $uri . '" title="' . __('overview.ov_message') . '"><span class=\'sprite skin_m\'></span></a>';
	}

	public static function isMobile(): bool
	{
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
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
