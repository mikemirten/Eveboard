<?php

use Phalcon\CLI\Task;

class MainTask extends Task {
	
	public function mainAction() {
		$this->killService->importKills();
	}
	
}