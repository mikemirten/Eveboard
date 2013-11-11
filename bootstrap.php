<?php
use Phalcon\Loader;
use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Mvc\View;
use Phalcon\DI\FactoryDefault as Di;
use Phalcon\Db\Adapter\Pdo\Mysql;

define('ROOT_PATH', __DIR__);

define('TMP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp');
define('LOG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'log');
define('LIB_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'library');
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'application');
define('CONF_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'configs');

date_default_timezone_set('Europe/Moscow');

$loader = new Loader();

$loader->registerNamespaces([
	'Eveboard' => LIB_PATH . DIRECTORY_SEPARATOR . 'Eveboard'
]);

$loader->registerDirs([
	APP_PATH . DIRECTORY_SEPARATOR . 'controllers',
	APP_PATH . DIRECTORY_SEPARATOR . 'models',
	APP_PATH . DIRECTORY_SEPARATOR . 'services'
]);

$loader->register();

$di = new Di();

$di->set('config', function() {
	return new Ini(CONF_PATH . DIRECTORY_SEPARATOR . 'settings.ini');
});

$di->set('db', function() use($di) {
	return new Mysql($di->get('config')->db->toArray());
});

$di->set('view', function() {
	$view = new View();
	$view->setViewsDir(APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
	$view->setMainView('layout');
	
	return $view;
});

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