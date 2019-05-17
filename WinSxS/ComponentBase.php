<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
class ComponentBase {
	const CBS = "HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Component Based Servicing\\";
	const CBS_PACKAGES = self::CBS . "Packages\\";
	const CBS_INDICES = self::CBS . "PackageIndex\\";
	const CBS_COMPONENT_DETECT = self::CBS . "ComponentDetect\\";
	const CBS_PACKAGE_DETECT = self::CBS . "PackageDetect\\";
	
	const TYPE_CATALOG = 'c';
	const TYPE_FILE = 'f';
	const TYPE_PACKAGE = 'p';
	
	private static function parseObjectType($name){
		$parts = explode("!", $name, 2);
		if(count($parts) != 2)
			return false;
		return $parts;
	}
	
	/** @var Registry **/
	protected $reg;
	/** @var RegistryKey **/
	protected $key;
	
	private $componentMap;
	
	protected function __construct(Registry $reg, RegistryKey $key){
		$this->reg = $reg;
		$this->key = $key;
	}
	
	protected function readBinaryString($name){
		$data = $this->key->read($name);
		return implode("", array_map("chr", $data));
	}
	
	protected function getComponentsByType($type){
		if(is_null($this->componentMap)){
			$this->componentMap = $this->getComponentMap();
		}
		
		return array_keys(array_filter($this->componentMap, function($types) use($type){
			return in_array($type, $types);
		}));
	}
	
	/**
	 * 
	 * @param RegistryKey $regKey
	 */
	protected function getComponentMap(){
		$map = array();
		
		foreach($this->key->values() as $name => $type){
			if($type != RegGlobals::REG_BINARY){
				continue;
			}
			
			list($type, $name) = self::parseObjectType($name);
			if(is_null($type))
				continue;
			
			if(!isset($map[$name])){
				$map[$name] = array();
			}
			$map[$name][] = $type;
		}
		
		return $map;
	}
	
	public function getName(){
		return $this->key->getName();
	}
	
	public function getKey() : RegistryKey {
		return $this->key;
	}
}
