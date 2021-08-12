<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WinSxS;

/**
 * Description of ComponentResolver
 *
 * @author sm
 */
class ComponentResolver {
	private $archIdentifier;
	private $reg;
	
	const COMPONENTS_KEY = "HKLM\\COMPONENTS\\DerivedData\\Components";
	
	/**
	 * adapted from https://stackoverflow.com/a/25196548/11782802
	 */
	private static function getProcessorArchitecture(){
		$wmi = new \COM('winmgmts:{impersonationLevel=impersonate}//./root/cimv2');

		if (!is_object($wmi)) {
			throw new \Exception('No access to WMI. Please enable DCOM in php.ini and allow the current user to access the WMI DCOM object.');
		}
		foreach($wmi->ExecQuery("SELECT Architecture FROM Win32_Processor") as $cpu) {
			# only need to check the first one (if there is more than one cpu at all)
			switch($cpu->Architecture) {
				case 0:
					return "x86";
				case 9: //amd64
					return "amd64";
				case 1: //mips
				case 2: //alpha
				case 3: //powerpc
				case 6: //itanium
				default:
					throw new \Exception("Unsupported CPU architecture");
			}
		}
	}
	
	public function __construct(){
		$this->archIdentifier = self::getProcessorArchitecture();
		$this->reg = new \Common\Registry;
	}
	
	public function findComponent($name) : ?Component {
		$components = $this->reg->enumerate(self::COMPONENTS_KEY);
		
		$archAndName = $this->archIdentifier . '_' . $name;
		
		$componentKey = \YaLinqo\Enumerable::from($components->keys())
				->where(function($key) use($archAndName){
					return stripos($key, $archAndName) === 0;
				})
				->firstOrDefault(null);
				
		if($componentKey == null){
			return null;
		}
		
		return new Component($this->reg, $components->getKey($componentKey));
	}
}
