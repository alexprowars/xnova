<?php

if (!defined('VERSION')) {
	define('VERSION', '5.0');
}

function log_var($name, $value)
{
	if (is_array($value)) {
		$value = var_export($value);
	}

	log_comment("$name = $value");
}

function log_comment($comment)
{
	echo "[log]$comment<br>\n";
}

$app = new Illuminate\Foundation\Application(
	realpath(__DIR__ . '/../')
);

$app->singleton(
	Illuminate\Contracts\Http\Kernel::class,
	Xnova\Http\Kernel::class
);
$app->singleton(
	Illuminate\Contracts\Console\Kernel::class,
	Xnova\Console\Kernel::class
);
$app->singleton(
	Illuminate\Contracts\Debug\ExceptionHandler::class,
	Xnova\Exceptions\Handler::class
);

return $app;
