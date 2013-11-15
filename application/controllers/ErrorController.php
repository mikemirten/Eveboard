<?php
use Phalcon\Mvc\Controller;

use Phalcon\Mvc\Dispatcher\Exception  as DispatcherException;

class ErrorController extends Controller {
	
	public function indexAction($exception = null) {
		$logText = date(DATE_RFC3339) . PHP_EOL . $exception . PHP_EOL . PHP_EOL;
		file_put_contents(LOG_PATH . DIRECTORY_SEPARATOR . 'exceptions.log', $logText, FILE_APPEND);
		
		if ($exception instanceof DispatcherException) {
			$this->response->setStatusCode(404, 'Not Found');
			$this->view->pick('error/404');
		} else {
			$this->response->setStatusCode(500, 'Internal Server Error');
			$this->view->pick('error/500');
		}
	}
	
}