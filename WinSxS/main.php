<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

class ComponentStore {
	public static function ensureLoaded() {
		$reg = System::getRegistry();
		if(!$reg->Enumerate("HKLM\\COMPONENTS")){
			system("REG LOAD HKLM\\COMPONENTS %windir%\\system32\\config\\COMPONENTS >NUL");
		}
	}
	
	public static function unload(){
		system("REG UNLOAD HKLM\\COMPONENTS >NUL");
	}
}

function handlePackage(Package $pkg){
	$tpl= <<<EOS
		[{$pkg->getKey()->getFullPath()}]
		=> Name        : {$pkg->getName()}
		=> Client      : {$pkg->getInstallClient()}
		=> Location    : {$pkg->getInstallLocation()}
		=> InstallName : {$pkg->getInstallName()}
		=> InstallUser : {$pkg->getInstallUser()}
		== Owners ==

EOS;

	$owners = $pkg->getOwnerPackages();
	foreach($owners as $ownerPkg){
		if($pkg->getName() != $ownerPkg->getName()){
			$tpl.= handlePackage($ownerPkg);
		}
	}
	
	return $tpl;
}

function iterManifests(){
	$root = System::getSysRoot();

	$iter = new DirectoryIterator("{$root}\\WinSxS\\Manifests");
	foreach($iter as $file){
		if(pathinfo($file, PATHINFO_EXTENSION) != "manifest")
			continue;

		$path = $file->getPathname();
		$manifest = new Manifest($path);

		$tpl = <<<EOS
	=== {$manifest->getName()} ===
	=> FilePath      : {$manifest->getFilePath()}
	
	EOS;

		$cmp = Component::fromName(System::getRegistry(), $manifest->getName());
		$depl = $cmp->getDeployment();
		$cat = $depl->getCatalog();
		
		$deployments = $cat->getDeployments();

		$tpl .= <<<EOS
	=== Component ===
	=> Name : {$cmp->getName()}
	=> Ident: {$cmp->getIdentity()}
	=== Catalog ===
	=> Name : {$cat->getName()}
	=== Deployment ===
	=> Name : {$depl->getName()}
	=> AppID: {$depl->getAppID()}
	=== Packages ===

EOS;
		foreach($depl->getPackages() as $pkg){
			$tpl .= handlePackage($pkg);
		}

		print($tpl);
	}
}

ComponentStore::ensureLoaded();
iterManifests();
ComponentStore::unload();