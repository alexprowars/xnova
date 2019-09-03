<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

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