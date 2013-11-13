<?php

use Phalcon\Config\Adapter\Ini;
use Phalcon\Mvc\View;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Eveboard\Api\Client as EveApiClient;

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

// Eve API Client
$di->set('eveApiClient', function() use($di) {
	$config = $di->get('config')->eveApi;
	
	$client =  new EveApiClient();
	$client->setKeyId($config->keyId);
	$client->setKeyCode($config->keyCode);
	
	return $client;
}, true);

// Kills processing service
$di->set('killService', function() use($di) {
	return new KillsService($di->get('eveApiClient'));
}, true);