<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

function p ($array)
{
	if (!defined('SUPERUSER'))
		return;

	if (is_object($array))
	{
		$t = clone $array;

		if (method_exists($t, 'setDi'))
			$t->setDi(new Phalcon\Di);

		echo '<pre>'; print_r($t); echo '</pre>';
		return;
	}

	echo '<pre>'; print_r($array); echo '</pre>';
}

function isMobile()
{
	if (!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT']))
		return false;

	return preg_match('/\b(?:a(?:ndroid|vantgo)|b(?:lackberry|olt|o?ost)|cricket|docomo|hiptop|i(?:emobile|p[ao]d)|kitkat|m(?:ini|obi)|palm|(?:i|smart|windows )phone|symbian|up\.(?:browser|link)|tablet(?: browser| pc)|(?:hp-|rim |sony )tablet|w(?:ebos|indows ce|os))/i', $_SERVER['HTTP_USER_AGENT']);
}

function convertIp ($ip)
{
	if (!is_numeric($ip))
		return sprintf("%u", ip2long($ip));
	else
		return long2ip($ip);
}

function is_email ($email)
{
	if (!$email)
		return false;

	if (preg_match('#^[^\\x00-\\x1f@]+@[^\\x00-\\x1f@]{2,}\.[a-z]{2,}$#iu', $email) == 0)
		return false;

	return true;
}

function log_var($name, $value)
{
	if (is_array($value))
		$value = var_export($value);

	log_comment("$name = $value");
}

function log_comment($comment)
{
	echo "[log]$comment<br>\n";
}

function is ($val, $key)
{
	return (isset($val[$key]) ? $val[$key] : '');
}

function _getText()
{
	$args = array_merge(['xnova'], func_get_args());

	return \Friday\Core\Lang::getText($args);
}

function getClassName ($className)
{
	$return = ['name' => '', 'namespace' => ''];

	$parts = explode('\\', $className);

	if (count($parts) > 1)
	{
		$return['name'] = $parts[count($parts) - 1];

		array_pop($parts);

		$return['namespace'] = implode('\\', $parts);
	}
	else
		$return['name'] = $className;

	return $return;
}