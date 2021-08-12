<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;

use Common\{Registry, System};
use WinSxS\Assembly\Assembly;

class Manifest  {
	private ?Assembly $assembly = null;
	private $manifestFilePath;
	
	private Component $component;
	private Wcp $wcp;
	
	public function getName(){
		return pathinfo($this->manifestFilePath, PATHINFO_FILENAME);
	}
	
	public function __construct($manifestFilePath){
		$this->manifestFilePath = $manifestFilePath;
		
		$this->component = Component::fromFullName(System::getRegistry(), $this->getName());
		$this->wcp = new Wcp();
	}
	
	public function getFilePath(){
		return $this->manifestFilePath;
	}
	
	public function getData() : Assembly {
		if(is_null($this->assembly)){
			$manifestData = $this->decompressDelta();
			$this->assembly = Assembly::fromData($manifestData);
		}
		return $this->assembly;
	}
	
	private function decompressDelta(){
		$manifestData = \file_get_contents($this->manifestFilePath);
		return $this->wcp->decompressData($manifestData);
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