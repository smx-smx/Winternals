<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WinSxS;

/**
 * Description of ComponentName
 *
 * @author sm
 */
class ComponentName {
	private string $archIdentifier;
	private string $qualifiedName;
	private string $publicKey;
	private string $version;
	private string $locale;
	private string $unkHash;
	
	public function __construct(string $arch, string $name, string $pubKey, string $version, string $locale, string $unkHash){
		$this->archIdentifier = $arch;
		$this->qualifiedName = $name;
		$this->publicKey = $pubKey;
		$this->version = $version;
		$this->unkHash = $unkHash;
	}
	
	public function __toString() {
		$parts = [$this->archIdentifier, $this->qualifiedName, $this->publicKey, $this->version, $this->locale, $this->unkHash];
		$parts = array_filter($parts, function($part){
			return !is_null($part);
		});
		return implode('_', $parts);
	}
}
