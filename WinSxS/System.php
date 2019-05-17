<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
class System {
	private static $reg;
	
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
		return self::getRegistry()
				->enumerate("HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion")
				->read("SystemRoot");
	}
}
