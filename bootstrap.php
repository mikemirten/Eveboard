<?php

use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault as Di;
use Phalcon\Events\Manager    as EventsManager;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Dispatcher;

require 'init.php';

$di = new Di();

require 'services.php';

// Dispatcher
$di->set('dispatcher', function() {
	$eventsManager = new EventsManager();

	$eventsManager->attach('dispatch:beforeDispatchLoop', function($event, $dispatcher) {
		// Params to Key/Value
		$paramsRaw = $dispatcher->getParams();
		$params    = [];
		
		while ($paramKey = array_shift($paramsRaw)) {
			if ($paramValue = array_shift($paramsRaw)) {
				$params[$paramKey] = $paramValue;
			}
		}
		
		$dispatcher->setParams($params);
	});
	
	$eventsManager->attach('dispatch:beforeException', function($event, $dispatcher, $exception) {
		$dispatcher->forward([
			'controller' => 'error',
			'action'     => 'index',
			'params'     => [$exception]
		]);

		return false;
	});

	$dispatcher = new Dispatcher();
	$dispatcher->setEventsManager($eventsManager);

	return $dispatcher;
}, true);

// Router
$di->set('router', function() {
    $router = new Router();

    $router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);
	$router->removeExtraSlashes(true);
	
    $router->clear();

    $router->setDefaultController('killboard');
    $router->setDefaultAction('index');
	
	$router->add('/:controller', [
		'controller' => 1,
		'action'     => 'index'
	])->setName('controler');
	
	$router->add('/:controller/:action/:params', [
		'controller' => 1,
		'action'     => 2,
		'params'     => 3
	])->setName('action');
	
    return $router;
}, true);

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