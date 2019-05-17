<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
class Component extends ComponentBase {
	public function __construct(Registry $reg, RegistryKey $key){
		parent::__construct($reg, $key);
	}
	
	public function getDeployment() : Deployment {
		$deployments = parent::getComponentsByType(ComponentBase::TYPE_CATALOG);
		if(count($deployments) > 1){
			throw new Exception("Expected single deployment for {$this->getName()}, found " . count($deployments));
		}
		
		$deploymentName = reset($deployments);
		return Deployment::fromName($this->reg, $deploymentName);
	}
	
	public function getFiles() : array {
		return parent::getComponentsByType(ComponentBase::TYPE_FILE);
	}
	
	public function getIdentity(){
		return parent::readBinaryString("identity");
	}
	
	public function getSha256(){
		return parent::readBinaryString("S256H");
	}
	
	public static function fromName(Registry $reg, $componentName){
		return new Component($reg, $reg->enumerate("HKLM\\COMPONENTS\\DerivedData\\Components\\{$componentName}"));
	}
}
