<?php

namespace Friday\Core;

class Helpers
{
	public static function phoneFormat ($phone)
	{
		if ($phone == '')
			return '';

		$phone = str_replace(['+', '-', '(', ')', ' '], '', $phone);

		return $phone;
	}

	public static function pastTimeFormat ($time, $currentTime = 0)
	{
		if (!$currentTime)
			$currentTime = time();

		$delta = $currentTime - $time;

		if ($delta < 60)
			return 'только что';
		elseif ($delta < 3600)
			return floor($delta / 60).' мин.';
		elseif ($delta < 86400)
			return floor($delta / 3600).' ч.';
		else
			return floor($delta / 86400).' дн.';
	}

	public static function getPlural ($n = 0, array $forms = [])
	{
		return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
	}

	public static function isPhone ($string = '')
	{
		return (!preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/iu', $string) ? false : true);
	}

	public static function downloadImage ($url, $filename)
	{
		if (file_exists($filename))
			@unlink($filename);

		$fp = fopen($filename, 'w');

		if (!$fp)
			return false;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);

		$result = parse_url($url);

		curl_setopt($ch, CURLOPT_REFERER, $result['scheme'].'://'.$result['host']);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$raw = curl_exec($ch);

		curl_close($ch);

		if ($raw)
			fwrite($fp, $raw);

		fclose($fp);

		if (!$raw)
		{
			@unlink($filename);

			return false;
		}

		return true;
	}

	static function translite($st)
	{
		$translit = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"zh","З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"shch","Ъ"=>"","Ы"=>"y","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya",
			"а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"zh","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"shch","ъ"=>"","ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
			"A"=>"a","B"=>"b","C"=>"c","D"=>"d","E"=>"e","F"=>"f","G"=>"g","H"=>"h","I"=>"i","J"=>"j","K"=>"k","L"=>"l","M"=>"m","N"=>"n","O"=>"o","P"=>"p","Q"=>"q","R"=>"r","S"=>"s","T"=>"t","U"=>"u","V"=>"v","W"=>"w","X"=>"x","Y"=>"y","Z"=>"z"
		);

		$result = strtr($st, $translit);
		$result = preg_replace("/[^a-zA-Z0-9_]/i","-",$result);
		$result = preg_replace("/\-+/i","-",$result);
		$result = preg_replace("/(^\-)|(\-$)/i","",$result);

		return $result;
	}
}