<?php

use Phalcon\Config\Adapter\Ini;
use Phalcon\Mvc\View;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher;
use Eveboard\Api\Client as EveApiClient;
use Phalcon\Mvc\Router;

// Global config
$di->set('config', function() {
	return new Ini(CONF_PATH . DIRECTORY_SEPARATOR . 'settings.ini');
}, true);

// Database
$di->set('db', function() use($di) {
	return new Mysql($di->get('config')->db->toArray());
}, true);

// View
$di->set('view', function() {
	$view = new View();
	$view->setViewsDir(APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
	$view->setMainView('layout');
	
	return $view;
}, true);

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

// Eve API Client
$di->set('eveApiClient', function() use($di) {
	$config = $di->get('config')->eveApi;
	
	$client =  new EveApiClient();
	$client->setKeyId($config->keyId);
	$client->setKeyCode($config->keyCode);
	
	$client->setOnSuccessCallback(function($section, $function, &$params, &$response) {
		if ($section === 'corp' && $function === 'KillLog') {
			file_put_contents(TMP_PATH . DIRECTORY_SEPARATOR . 'killog_' . date('Y-m-d_H:i:s') . '.xml', $response);
		}
	});
	
	return $client;
}, true);

// Kills processing service
$di->set('killService', function() use($di) {
	return new KillsService($di->get('eveApiClient'));
}, true);