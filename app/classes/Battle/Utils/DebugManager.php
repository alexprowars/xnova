<?php

namespace App\Battle\Utils;

class DebugManager
{
	private $errorHandler;
	private $exceptionHandler;

	public static function intercept($toIntercept, $newFunction)
	{
		return function ()use($toIntercept, $newFunction)
		{
			$newFunction();
			return call_user_func_array($toIntercept, func_get_args());
		}
		;
	}
	/**
	 * DebugManager::runDebugged()
	 * Return a new function that will run the function given as argument under debug
	 * @param callable $func
	 * @return callable
	 */
	public static function runDebugged($func, $errorHandler = null, $exceptionHandler = null)
	{
		if ($errorHandler == null)
			$errorHandler = array('DebugManager', 'myErrorHandler');
		if ($exceptionHandler == null)
			$exceptionHandler = array('DebugManager', 'save');
		return function ()use($func, $errorHandler, $exceptionHandler)
		{
			set_error_handler($errorHandler);
			set_exception_handler($exceptionHandler);
			$return = call_user_func_array($func, func_get_args($func));
			restore_exception_handler();
			restore_error_handler();
			return $return;
		}
		;
	}

	/**
	 * DebugManager::myErrorHandler()
	 * default error handler function
	 * @param mixed $errno
	 * @param mixed $errstr
	 * @param mixed $errfile
	 * @param mixed $errline
	 * @return
	 */
	public static function myErrorHandler($errno, $errstr, $errfile, $errline)
	{
		$error = '';
		switch ($errno)
		{
			case E_USER_ERROR:
				$error .= "ERROR [$errno] $errstr" . PHP_EOL;
				break;

			case E_USER_WARNING:
				$error .= "WARNING [$errno] $errstr" . PHP_EOL;
				break;

			case E_USER_NOTICE:
				$error .= "NOTICE [$errno] $errstr" . PHP_EOL;
				break;

			default:
				$error .= "Unknown error type: [$errno] $errstr" . PHP_EOL;
				break;
		}
		$error .= "Error on line $errline in file $errfile";
		$error .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL;
		DebugManager::save($error);
		/* Don't execute PHP internal error handler */
		return true;

	}

	/**
	 * DebugManager::save()
	 * default exception handler function
	 * @param mixed $other
	 * @return
	 */
	public static function save($other)
	{
		date_default_timezone_set(TIMEZONE);
		$time = date('l jS \of F Y h:i:s A');
		$post = '$_POST =' . var_export($_POST);
		$get = '$_GET =' . var_export($_GET);
		$output = ob_get_clean();
		if (!file_exists(OPBEPATH . 'errors'))
		{
			mkdir(OPBEPATH . 'errors', 0777, true);
		}
		file_put_contents(OPBEPATH . 'errors' . DIRECTORY_SEPARATOR . date('d-m-y__H-i-s') . '.html', $time . PHP_EOL . $other . PHP_EOL . $post . PHP_EOL . $get . PHP_EOL . $output);
		die('An error occurred, we will resolve it soon as possible');
	}
}

function log_var($name, $value)
{
	if (is_array($value))
	{
		$value = var_export($value);
	}
	log_comment("$name = $value");
}
function log_comment($comment)
{
	echo "[log]$comment<br>\n";
}

?>