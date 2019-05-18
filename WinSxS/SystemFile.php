<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;

use Common\System;

class SystemFile {
	private $filePath;
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
		$componentPath = $this->getHardLinks()[0];
		$componentName = basename(dirname($componentPath));
		return Component::fromName(System::getRegistry(), $componentName);
	}
}
