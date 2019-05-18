<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;
use Common\Registry;
use Common\RegistryKey;

class Catalog extends ComponentBase {	
	public function __construct(Registry $reg, RegistryKey $key) {
		parent::__construct($reg, $key);
	}
	
	public function getDeployments(){
		$reg = $this->reg;
		return array_map(function($name) use($reg){
			return Deployment::fromName($reg, $name);
		}, parent::getComponentsByType(ComponentBase::TYPE_CATALOG));
	}
	
	public static function fromName(Registry $reg, $catalogName){
		$catKey = $reg->enumerate("HKLM\\COMPONENTS\\CanonicalData\\Catalogs\\{$catalogName}");
		return new Catalog($reg, $catKey);
	}
}
