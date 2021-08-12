<?php
namespace Common;

use \Variant;

/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
class RegistryKey {
	private Registry $reg;
	
	private $keys = array();
	private $values = array();
	
	private $valuesLower = array();
	
	private string $parentHive;
	private string $parentPath;
	
	public function __construct($reg, $parentKey, $keys, $values){
		$this->reg = $reg;
		$this->keys = $keys;
		$this->values = $values;
		
		$this->valuesLower = array_combine(
				array_map("strtolower", array_keys($values)),
				array_values($values));
		
		list($this->parentHive, $this->parentPath) = explode("\\", $parentKey, 2);
	}
	
	public function getKey($keyName) {
		$path = $this->getFullPath() . "\\{$keyName}";
		return $this->reg->enumerate($path);
	}
	
	public function getHive(){
		return $this->parentHive;
	}
	
	public function getRelativePath(){
		return $this->parentPath;
	}
	
	public function getName(){
		return basename($this->parentPath);
	}
	
	public function getFullPath(){
		return "{$this->parentHive}\\{$this->parentPath}";
	}
	
	public function keys(){
		return $this->keys;
	}
	
	public function values(){
		return $this->values;
	}
	
	public function read($valueName){
		$valueKey = strtolower($valueName);
		if(!isset($this->valuesLower[$valueKey])){
			return false;
		}
		
		$valueType = $this->valuesLower[$valueKey];
		$comFunc = RegGlobals::getFunctionForKeyType($valueType);
		$hiveValue = RegGlobals::hiveEnumValue($this->parentHive);		
		
		$result = new Variant;
		$prov = $this->reg->getProvider();
		$prov->{$comFunc}($hiveValue, $this->parentPath, $valueName, $result);
		
		switch($valueType){
			case RegGlobals::REG_BINARY:
			case RegGlobals::REG_MULTI_SZ:
				return RegGlobals::toArray($result);
			default:
				return $result;
		}
		return RegGlobals::unbox($result);
	}
}
