<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;
use Common\{Registry, RegistryKey};

class Package extends ComponentBase {
	
	public function __construct(Registry $reg, RegistryKey $key){
		parent::__construct($reg, $key);
	}
	
	public function getInstallClient(){
		return $this->key->read("InstallClient");
	}
	public function getInstallName(){
		return $this->key->read("InstallName");
	}
	public function getInstallLocation(){
		return $this->key->read("InstallLocation");
	}
	public function getInstallUser(){
		return $this->key->read("InstallUser");
	}
	
	public function getChildPackages() : array{
		list($packageName, $publicKey, $arch, $lang, $version) = self::parseName($this->getName());
		
		$versionLess = self::makeName($packageName, $publicKey, $arch, $lang, "0.0.0.0");
		$key = $this->reg->enumerate(self::CBS_PACKAGE_DETECT . $versionLess);
		
		$reg = $this->reg;
		return array_map(function($package) use($reg){
			return self::fromNameAndVersion($reg, $package);
		}, array_keys($key->values()));
	}
	
	public function getOwnerPackages() : array {
		$key = $this->reg->enumerate($this->key->getFullPath() . "\\Owners");
		$reg = $this->reg;
		return array_map(function($package) use($reg){
			return self::fromNameAndVersion($reg, $package);
		}, array_keys($key->values()));
	}
	
	public static function makeName($packageName, $publicKey, $arch, $lang, $version){
		return "{$packageName}~{$publicKey}~{$arch}~{$lang}~{$version}";
	}
	
	public static function parseName($name){
		return explode("~", $name, 5);
	}
	
	public static function fromNameAndVersion(Registry $reg, $packageName){
		if(strpos($packageName, "CBS_") === 0){
			throw new Exception("CBS Package names are not allowed");
		}
				
		return new Package($reg, $reg->enumerate(self::CBS_PACKAGES . $packageName));
	}
	
	public static function getSystemPackage(Registry $reg){
		$key = $reg->enumerate(self::CBS_INDICES . "System");
		$packageName = array_key_first($key->values());
		return self::fromNameAndVersion($reg, $packageName);
	}
	
	public static function getProductPackage(Registry $reg){
		$key = $reg->enumerate(self::CBS_INDICES . "Product");
		$packageName = array_key_first($key->values());
		return self::fromNameAndVersion($reg, $packageName);
	}
}
