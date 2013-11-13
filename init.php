<?php

use Phalcon\Loader;

define('ROOT_PATH', __DIR__);

define('TMP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp');
define('LOG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'log');
define('LIB_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'library');
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'application');
define('CONF_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'configs');

date_default_timezone_set('Europe/Moscow');

register_shutdown_function(function() {
	$error = error_get_last();

	$fatals = [
		E_USER_ERROR      => 'Fatal Error',
		E_ERROR           => 'Fatal Error',
		E_PARSE           => 'Parse Error', 
		E_CORE_ERROR      => 'Core Error',
		E_CORE_WARNING    => 'Core Warning',
		E_COMPILE_ERROR   => 'Compile Error',
		E_COMPILE_WARNING => 'Compile Warning'
	];
	
	if (! empty($error)) {
		$logText = implode(PHP_EOL, [date(DATE_RFC3339), $error['message'], $error['file'], PHP_EOL]);
		file_put_contents(LOG_PATH . DIRECTORY_SEPARATOR . 'errors.log', $logText, FILE_APPEND);
		
		if (! isset($_SERVER['argv']) && isset($fatals[$error['type']])) {
			header("HTTP/1.1 500 Internal Server Error", true, 500);
			echo '<html><body><p>Fatal error occurred</p></body></html>';
		}
	}
});

$loader = new Loader();

$loader->registerNamespaces([
	'Eveboard' => LIB_PATH . DIRECTORY_SEPARATOR . 'Eveboard'
]);

$loader->registerDirs([
	APP_PATH . DIRECTORY_SEPARATOR . 'controllers',
	APP_PATH . DIRECTORY_SEPARATOR . 'models',
	APP_PATH . DIRECTORY_SEPARATOR . 'services',
	APP_PATH . DIRECTORY_SEPARATOR . 'tasks'
]);

$loader->register();