<?php

use Phalcon\CLI\Console;
use Phalcon\Di\FactoryDefault\Cli as Di;

require 'init.php';

$di = new Di();

require 'services.php';

$console = new Console($di);

try {
	$console->handle();
} catch (Exception $e) {
	$logText = date(DATE_RFC3339) . PHP_EOL . $e . PHP_EOL . PHP_EOL;
	file_put_contents(LOG_PATH . DIRECTORY_SEPARATOR . 'exceptions.log', $logText, FILE_APPEND);
	
	echo '[', $e->getCode(), '] ', $e->getMessage(), PHP_EOL;
	return $e->getCode();
}

echo PHP_EOL;
return 0;