<?php
namespace Common;

/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
class System {
	private static $reg;
	
	private static $systemRoot = null;
	private static $currentControlSet = null;

	/**
	 * 
	 * @return Registry
	 */
	public static function getRegistry(){
		if(is_null(self::$reg)){
			self::$reg = new Registry();
		}
		return self::$reg;
	}
	
	public static function getSysRoot(){
		if(!is_null(self::$systemRoot)){
			return self::$systemRoot;
		}
		return self::getRegistry()
				->enumerate("HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion")
				->read("SystemRoot");
	}
	
	public static function getCurrentControlSet(){
		if(!is_null(self::$currentControlSet)){
			return self::$currentControlSet;
		}
		return self::getRegistry()
				->enumerate("HKLM\\SYSTEM\\Select")
				->read("Current");
	}
}
