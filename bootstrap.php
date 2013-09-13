<?php
use Eveboard\Killmail\Parser;

define('ROOT_PATH', __DIR__);

define('TMP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp');
define('LIB_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'library');

$loader = new Phalcon\Loader();

$loader->registerNamespaces([
	'Eveboard' => LIB_PATH . DIRECTORY_SEPARATOR . 'Eveboard'
]);

$loader->register();

$parser = new Parser(file_get_contents(TMP_PATH . '/killmail1'));

var_dump($parser->toArray());