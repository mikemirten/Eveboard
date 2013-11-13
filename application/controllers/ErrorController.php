<?php
use Phalcon\Mvc\Controller;

class ErrorController extends Controller {
	
	public function indexAction($exception = null) {
		$logText = date(DATE_RFC3339) . PHP_EOL . $exception . PHP_EOL . PHP_EOL;
		file_put_contents(LOG_PATH . DIRECTORY_SEPARATOR . 'exceptions.log', $logText, FILE_APPEND);
	}
	
}