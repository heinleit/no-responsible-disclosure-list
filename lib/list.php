<?php

include_once(__DIR__ . "/unverantwortli-ch.php");

use \unverantwortli\ch\Unverantwortlich;
use \unverantwortli\ch\Fool;

class NRDList {
	
	private $last_update;
	private $unv;
	
	public function __construct() {
		$this->unv = new Unverantwortlich("https://github.com/unverantwortli-ch/list.git", __DIR__ . "/list");
		
		if(file_exists(__DIR__ . "/updated")) {
			$this->last_update = (int) file_get_contents(__DIR__ . "/updated");
		}
		
		$minutes = 5;
		if((time() - 60*$minutes) >= $this->last_update) {
			$this->loadFromGit();
			$this->pushToGit();
			file_put_contents(__DIR__ . "/updated", time());
		}
	}
	
	public function addEntry(string $name, int $year, string $proof) {
		$this->unv->addFool(new Fool($name, $year, [$proof]));
	}
	
	public function getEntries() {
		return $this->unv->getFools();
	}
	
	public function loadFromGit() {
		$this->unv->pullBranch();
	}
	
	public function pushToGit() {
		$this->unv->pushBranch();
	}
}

