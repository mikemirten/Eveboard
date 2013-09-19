<?php
use Phalcon\Loader;
use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Mvc\View;
use Phalcon\DI\FactoryDefault as Di;

define('ROOT_PATH', __DIR__);

define('TMP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp');
define('LIB_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'library');
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'application');
define('CONF_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'configs');

$loader = new Loader();

$loader->registerNamespaces([
	'Eveboard' => LIB_PATH . DIRECTORY_SEPARATOR . 'Eveboard'
]);

$loader->registerDirs([
	APP_PATH . DIRECTORY_SEPARATOR . 'controllers'
]);

$loader->register();

$di = new Di();

$di->set('view', function() {
	$view = new View();
	$view->setViewsDir(APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
	$view->setMainView('layout');
	
	return $view;
});

$di->set('config', function() {
	return new Ini(CONF_PATH . DIRECTORY_SEPARATOR . 'settings.ini');
});

$application = new Application($di);

echo $application->handle()->getContent();