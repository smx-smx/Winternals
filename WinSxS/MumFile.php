<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;

use DOMDocument;
use DOMElement;
use DOMXPath;
use DOMNodeList;

class MumFile {
	private $filePath;
	
	private DOMDocument $dom;
	private DOMElement $root;
	
	public function __construct($filePath){
		$this->filePath = $filePath;
		
		$this->dom = new DOMDocument();
		$this->dom->load($filePath);
		
		if(!$this->validate()){
			throw new \Exception("Invalid or unsupported MUM file");
		}
		
		$this->root = $this->dom->documentElement;
	}
	
	public function getDescription(){
		return $this->root->getAttribute("description");
	}
	
	public function getDisplayName(){
		return $this->root->getAttribute("displayName");
	}
	
	public function getCompany(){
		return $this->root->getAttribute("company");
	}
	
	public function getSupportInformation(){
		return $this->root->getAttribute("supportInformation");
	}
	
	private static function getAttributesList(DOMElement $el, array $attribs){
		return array_map(function($attr) use($el){
			return $el->getAttribute($attr);
		}, $attribs);
	}
	
	public function getAssemblyIdentity(){
		/** @var DOMElement $ident **/
		$ident = $this->root->getElementsByTagName("assemblyIdentity")->item(0);
		
		
		$keys = array(
			"name",
			"version",
			"language",
			"processorArchitecture",
			"publicKeyToken",
			"buildType"
		);
		return array_combine($keys, self::getAttributesList($ident, $keys));
	}
	
	private function xpath($query){
		$xp = new DOMXPath($this->dom);
		$xp->registerNamespace("mum", "urn:schemas-microsoft-com:asm.v3");
		return $xp->query($query);
	}
	
	public function getPackages() {		
		$keys = array("identifier", "releaseType");
		
		$packages = array();
		$nodes = $this->xpath("/mum:assembly/mum:package");
		$n = $nodes->count();
		for($i=0; $i<$n; $i++){
			/** @var DOMElement $node **/
			$node = $nodes->item($i);
			
			$props = array_combine($keys, self::getAttributesList($node, $keys));
			$packages[$props["identifier"]] = $props;
		}
		return $packages;
	}
	
	private function validate(){
		$root = $this->dom->documentElement;
		if($root->tagName != "assembly")
			return false;
		
		if($root->getAttribute("xmlns") != "urn:schemas-microsoft-com:asm.v3")
			return false;
		
		if($root->getAttribute("manifestVersion") != "1.0"){
			return false;
		}
		
		return true;
	}
}
