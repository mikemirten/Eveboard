<?php

use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault as Di;

require 'init.php';

$di = new Di();

require 'services.php';

$application = new Application($di);

try {
	$response = $application->handle();
} catch (Exception $e) {
	$logText = date(DATE_RFC3339) . PHP_EOL . $e . PHP_EOL . PHP_EOL;
	file_put_contents(LOG_PATH . DIRECTORY_SEPARATOR . 'exceptions.log', $logText, FILE_APPEND);
	
	header("HTTP/1.1 500 Internal Server Error", true, 500);
	echo '<html><body><p>Fatal error occurred</p></body></html>';
	return;
}

echo $response->getContent();