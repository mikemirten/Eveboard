<?php
use Phalcon\Mvc\Controller;

class IndexController extends Controller {
	
	public function indexAction() {
		$source = file_get_contents(TMP_PATH . DIRECTORY_SEPARATOR . 'killmail7');
		
		$kills = new KillsService();
		
		$kills->addKill($source);
	}
	
}