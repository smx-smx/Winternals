<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;

use Common\{Registry, System};

class Manifest  {
	private $manifestData;
	private $manifestFilePath;
	
	/** @var Component **/
	private $component;
	
	public function getName(){
		return pathinfo($this->manifestFilePath, PATHINFO_FILENAME);
	}
	
	public function __construct($manifestFilePath){
		$this->manifestFilePath = $manifestFilePath;
		
		$this->component = Component::fromName(System::getRegistry(), $this->getName());
	}
	
	public function getFilePath(){
		return $this->manifestFilePath;
	}
	
	public function getData(){
		if(is_null($this->manifestData)){
			$this->manifestData = $this->decompressDelta();
		}
		return $this->manifestData;
	}
	
	private function decompressDelta(){
		$out = shell_exec(__DIR__ . "/internals/undelta.exe " . escapeshellarg($this->manifestFilePath));
		return substr($out, strpos($out, "<?xml"));
	}

	/**
	 * 
	 * @param type $manifestName
	 * @return \Manifest
	 */
	public static function fromName($manifestName){
		$sysRoot = System::getSysRoot();
		return new Manifest("{$sysRoot}\\WinSxS\\Manifests\\{$manifestName}.manifest");
	}
	
	public function getComponent() : Component {
		return $this->component;
	}
}