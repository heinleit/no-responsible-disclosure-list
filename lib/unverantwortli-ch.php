<?php

namespace unverantwortli\ch;

error_reporting(E_ERROR | E_PARSE);
require_once __DIR__.'/vendor/autoload.php';

use \Dallgoot\Yaml;
use \CzProject\GitPhp\Git;
use \CzProject\GitPhp\GitRepository;


class Unverantwortlich {
	private string $repoUrl;
	private string $repoDir;
	private string $listFile;
	private GitRepository $repo;
	
	public function __construct(string $repoUrl, string $repoDir) {
		$this->repoUrl = $repoUrl;
		$this->repoDir = $repoDir;
		$this->listFile = $this->repoDir . DIRECTORY_SEPARATOR . "list.yml";
		
		$git = new Git();
		if(!file_exists($this->repoDir) 
			|| !is_dir($this->repoDir)) {
			mkdir($this->repoDir);
			$git->cloneRepository($repoUrl, $this->repoDir);
		}
		
		$this->repo = $git->open($this->repoDir);
	}
	
	public function getFools() : array {
		$content = Yaml::parseFile($this->listFile, 0, 0);
		return array_map(function($f) {
			return new Fool($f->name, $f->year, $f->proofs);
		}, $content->fools);
	}
	
	public function addFool(Fool $fool) {
		$fools = $this->getFools();
		$fools[] = $fool;
		$obj = new \stdClass();
		$obj->fools = $fools;
		$text = Yaml::dump($obj)."\n";
		file_put_contents($this->listFile, $text);
		$this->repo->addFile($this->listFile);
		$this->repo->commit("add " . $fool->name);
	}
	
	public function pullBranch() {
		$this->repo->pull();
		$this->repo->checkout("bsts");
	}
	
	public function pushBranch() {
		$this->repo->push();
	}
	
}

class Fool {
	public string $name;
	public string $year;
	public array $proofs;
	
	public function __construct(string $name, string $year, array $proofs) {
		$this->name = $name;
		$this->year = $year;
		$this->proofs = $proofs;
	}
}


//$c = new Unverantwortlich("https://github.com/unverantwortli-ch/list.git", __DIR__ . "/repo1");
//// $c->addFool(new Fool("bla", "2020", ["bla"]));
//// $c->pushBranch();
//$c->pullBranch();
//print_r($c->getFools());