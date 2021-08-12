<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;

use Common\System;
use \YaLinqo\Enumerable;

class SystemFile {
	private string $filePath;
	public function __construct($filePath) {		
		$this->filePath = str_replace("/", "\\", $filePath);
	}
	
	public function getHardLinks(){
		if(!preg_match("/(^.*:)(\\\\.*)/", $this->filePath, $m)){
			return false;
		}
		$path = strtolower($m[2]);
		
		$result = [];
		
		$h = popen("fsutil hardlink list \"{$this->filePath}\"", "r");
		while(!feof($h)){
			$line = rtrim(fgets($h));
			if(empty($line))
				continue;
			if($path == strtolower($line))
				continue;
			
			$result[] = $m[1] . $line;
		}
		pclose($h);
		
		return $result;
	}
	
	public function getComponent() : Component {
		$componentStorePath = System::getSysRoot();
		if(strrchr($componentStorePath, "\\") !== "\\"){
			$componentStorePath .= "\\";
		}
		$componentStorePath .= "WinSxS";

		$links = $this->getHardLinks();
		
		$componentPath = Enumerable::from($links)->where(function($path) use($componentStorePath){
			return stripos($path, $componentStorePath) === 0;
		})->firstOrDefault(null);
		
		if(is_null($componentPath)){
			return null;
		}

		$componentName = basename(dirname($componentPath));
		return Component::fromFullName(System::getRegistry(), $componentName);
	}
}
