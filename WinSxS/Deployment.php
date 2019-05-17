<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
class Deployment extends ComponentBase {	
	public function __construct(Registry $reg, RegistryKey $key) {
		parent::__construct($reg, $key);
	}
	
	public function getCatalog() : Catalog {
		$thumbPrint = $this->getCatalogThumbprint();
		return Catalog::fromName($this->reg, $thumbPrint);
	}
	
	public function getPackages(){
		$result = array();
		
		$pkgNames = parent::getComponentsByType(ComponentBase::TYPE_PACKAGE);
		foreach($pkgNames as $name){
			$data = parent::readBinaryString("p!{$name}");
			
			// skip unknown 8 bytes header made of 2 little endian integers
			$packageName = substr($data, 8);	

			// version has an extra dot appended we more infos. get rid of it
			// $TODO: handle it instead
			list($packageName, $publicKey, $arch, $lang, $version) = Package::parseName($packageName);
			list($ver1, $ver2, $ver3, $ver4, $extra) = explode(".", $version);			
			
			$version = "{$ver1}.{$ver2}.{$ver3}.{$ver4}";
			$packageName = Package::makeName($packageName, $publicKey, $arch, $lang, $version);
			
			$pkg = Package::fromNameAndVersion($this->reg, $packageName);
			$result[] = $pkg;
		}
		
		return $result;
	}
	
	public function getAppID(){
		return parent::readBinaryString("appid");
	}
	
	public function getCatalogThumbprint(){
		return $this->key->read("CatalogThumbprint");
	}
	
	
	public static function fromName(Registry $reg, $deploymentName) : Deployment {
		return new Deployment($reg, $reg->enumerate("HKLM\\COMPONENTS\\CanonicalData\\Deployments\\{$deploymentName}"));
	}
}
