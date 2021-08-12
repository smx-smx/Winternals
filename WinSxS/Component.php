<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;
use Common\{Registry, RegistryKey};

class Component extends ComponentBase {
	public function __construct(Registry $reg, RegistryKey $key){
		parent::__construct($reg, $key);
	}
	
	public function getDeployments() : array {
		$deployments = parent::getComponentsByType(ComponentBase::TYPE_CATALOG);
		
		return array_map(function($deploymentName){
			return Deployment::fromName($this->reg, $deploymentName);
		}, $deployments);
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
	
	public static function fromFullName(Registry $reg, $componentFullName) : Component {
		return new Component($reg, $reg->enumerate("HKLM\\COMPONENTS\\DerivedData\\Components\\{$componentFullName}"));
	}
	
	public static function fromName(Registry $reg, $nameOnly){
		$components = $reg->enumerate("HKLM\\COMPONENTS\\DerivedData\\Components");
		
		return \YaLinqo\Enumerable::from($components->keys())
				->where(function($key) use($nameOnly){
					return stripos($key, $nameOnly) === 0;
				})
				->firstOrDefault(null);
	}
}
