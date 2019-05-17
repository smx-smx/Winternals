<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
class RegGlobals {
	const REG_SZ = 1;
	const REG_EXPAND_SZ = 2;
	const REG_BINARY = 3;
	const REG_DWORD = 4;
	const REG_MULTI_SZ = 11;
	const REG_QWORD = 11;
	
	private static $hivesLongShort = array(
		"HKEY_CLASSES_ROOT" => "HKCR",
		"HKEY_CURRENT_USER" => "HKCU",
		"HKEY_LOCAL_MACHINE" => "HKLM",
		"HKEY_USERS" => "HKU",
		"HKEY_CURRENT_CONFIG" => "HKCC"
	);
	
	private static $hivesShortHex = array(
		"HKCR" => 0x80000000,
		"HKCU" => 0x80000001,
		"HKLM" => 0x80000002,
		"HKU" => 0x80000003,
		"HKCC" => 0x80000005
	);
	
	private static $typeFuncMap = array(
		self::REG_SZ => "GetStringValue",
		self::REG_EXPAND_SZ => "GetExpandedStringValue",
		self::REG_BINARY => "GetBinaryValue",
		self::REG_DWORD => "GetDWORDValue",
		self::REG_MULTI_SZ => "GetMultiStringValue",
		self::REG_QWORD => "GetQWORDValue"
	);
	
	//https://stackoverflow.com/a/10841902
	private static function signedint32($value) {
		$i = (int)$value;
		if (PHP_INT_SIZE > 4)   // e.g. php 64bit
			if($i & 0x80000000){ // is negative
				return $i - 0x100000000;
			}
		return $i;
	}
	
	public static function hiveEnumValue($shortName){
		return new Variant(self::signedint32(
				self::$hivesShortHex[$shortName]
		), VT_I4);
	}
	
	public static function hiveShortName($shortName){
		return self::$hivesLongShort[$shortName];
	}
	
	public static function getFunctionForKeyType($keyType){
		return self::$typeFuncMap[$keyType];
	}
	
	public static function unbox($comObj){
		switch(($type=variant_get_type($comObj))){
			case VT_ARRAY:
				return self::toArray($comObj);
			case VT_I1:
			case VT_I2:
			case VT_I4:
			case VT_UI1:
			case VT_UI2:
			case VT_UI4:			
				return intval($comObj);
			case VT_BOOL:
				return boolval($comObj);
			case VT_NULL:
				return null;
			case VT_BSTR:
				return strval($comObj);
			default:
				throw new Exception("not implemented " . $type);
		}
	}
	
	public static function toArray($comObj){
		$res = [];
		if(variant_get_type($comObj) == VT_NULL){
			return $res;
		}
		
		foreach($comObj as $obj){
			$res[] = $obj;
		}
		return $res;
	}
}

class Registry {
	
	private $shell;
	private $regProv;
	
	public function getProvider(){
		return $this->regProv;
	}
	
	public function __construct(){
		$this->regProv = new COM("winmgmts://./root/default:StdRegProv") or die("Requires StdRegProv");
	}
	
	public function enumerate($key){
		$key = $this->toShortKeyPath($key);
		
		list($root, $rest) = explode("\\", $key, 2);
		$root = RegGlobals::hiveEnumValue($root);
				
		$subKeys = new Variant;
		$this->regProv->EnumKey($root, $rest, $subKeys);
		$keys = RegGlobals::toArray($subKeys);
		
		$names = new Variant;
		$types = new Variant;

		if($this->regProv->EnumValues($root, $rest, $names, $types) != 0){
			return false;
		}
		
		$names = RegGlobals::toArray($names);
		$types = RegGlobals::toArray($types);
		
		$values = array();
		for($i=0; $i<count($names); $i++){
			$values[$names[$i]] = $types[$i];
		}
		
		return new RegistryKey($this, $key, $keys, $values);
	}
	
	public function toShortKeyPath($name){
		list($root, $rest) = explode("\\", $name, 2);
		
		if(isset($this->hivesLongShort[$root])){
			$name = $this->hivesLongShort[$root] . "\\" . $rest;
		}
		return $name;
	}	
}