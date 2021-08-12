<?php
/**
 * Copyright 2019 Stefano Moioli <smxdev4@gmail.com>
 */
namespace WinSxS;

class LBLOB {
	private $t_LBLOB;
	private $hBLOB;
	private $ownedData = false;

	private static function getType(){
		return \FFI::type("
			struct {
				size_t length;
				size_t fill;
				unsigned char* pData;
			}"
		);
	}

	public static function fromData($data){
		$length = strlen($data);
		$dataType = \FFI::arrayType(
			\FFI::type("unsigned char"), [$length]
		);
		
		$nativeData = \FFI::new($dataType, false);
		\FFI::memcpy($nativeData, $data, $length);

		$hBlob = new LBLOB();
		$hBlob->ownedData = true;

		$ptr = $hBlob->getBlobPtr();
		$ptr->length = $length;
		$ptr->fill = $length;
		$ptr->pData = \FFI::cast("unsigned char *", \FFI::addr($nativeData));
		return $hBlob;
	}

	public function getLength(){
		return $this->getBlob()->length;
	}

	public function getData(){
		$data = "";

		$length = $this->getLength();
		$dptr = $this->getBlob()->pData;

		for($i=0; $i<$length; $i++){
			$data .= chr($dptr[$i]);
		}

		return $data;
	}

	public function getBlob(){
		return $this->hBLOB;
	}

	public function getBlobPtr(){
		return \FFI::addr($this->hBLOB);
	}

	public function __destruct(){
		if($this->hBLOB->pData != null && $this->ownedData){
			\FFI::free($this->hBLOB->pData);
		}
	}

	public function __construct(){
		$this->hBLOB = \FFI::new(self::getType());
	}
}

class Wcp {
	private $ctx;
	private $hWcp;

	private $pfnInitialize;
	private $pfnGetCompressedFileType;
	private $pfnLoadFirstResourceLanguageAgnostic;
	private $pfnDeltaDecompressBuffer;

	private function LoadLibraryA($libPath){
		return $this->ctx->LoadLibraryA($libPath);
	}

	private function GetProcAddress($handle, $funcName){
		return $this->ctx->GetProcAddress($handle, $funcName);
	}

	public function __construct(){
		$this->ctx = \FFI::cdef(
			"
			typedef void * HMODULE;
			typedef char * LPCSTR;
			typedef void (*FARPROC)();
			typedef int BOOL;
			typedef unsigned int DWORD;

			HMODULE LoadLibraryA(LPCSTR lpLibFileName);
			FARPROC GetProcAddress(HMODULE hModule, LPCSTR  lpProcName);
			BOOL FreeLibrary(HMODULE hLibModule);
			DWORD GetLastError();
			"
			,
			"kernel32.dll"
		);

		$this->hWcp = $this->LoadLibraryA("wcp.dll");

		$this->pfnInitialize = \FFI::cast(
			"long (*)(void *)",
			$this->GetProcAddress($this->hWcp, "?InitializeDeltaCompressor@Rtl@Windows@@YAJPEAX@Z")
		);
		$this->pfnGetCompressedFileType = \FFI::cast(
			"long (*)(void *)",
			$this->GetProcAddress($this->hWcp, "?GetCompressedFileType@Rtl@WCP@Windows@@YAKPEBU_LBLOB@@@Z")
		);

		$this->pfnLoadFirstResourceLanguageAgnostic = \FFI::cast(
			"long (*)(void *, void *, unsigned short *, unsigned short *, void *)",
			$this->GetProcAddress($this->hWcp, "?LoadFirstResourceLanguageAgnostic@Rtl@Windows@@YAJKPEAUHINSTANCE__@@PEBG1PEAU_LBLOB@@@Z")
		);

		$this->pfnDeltaDecompressBuffer = \FFI::cast(
			"long (*)(long, void *, long, void *, void *)",
			$this->GetProcAddress($this->hWcp, "?DeltaDecompressBuffer@Rtl@Windows@@YAJKPEAU_LBLOB@@_K0PEAVAutoDeltaBlob@12@@Z")
		);
	}

	private function initialize(){
		return ($this->pfnInitialize)(null);
	}

	private function getCompressedFileType($hblob){
		return ($this->pfnGetCompressedFileType)($hblob->getBlobPtr());
	}

	private function loadDictionary(){
		$dictionary = new LBLOB();
		$lpName = 0x266;
		$lpType = 0x1;

		$ret = ($this->pfnLoadFirstResourceLanguageAgnostic)(
			null,
			$this->hWcp,
			\FFI::cast("unsigned short *", $lpName),
			\FFI::cast("unsigned short *", $lpType),
			$dictionary->getBlobPtr()
		);

		return $dictionary;
	}

	private function decompressBlob($dictionaryBlob, $inputBlob){
		$outputBlob = new LBLOB();
		$ret = ($this->pfnDeltaDecompressBuffer)(
			2,
			$dictionaryBlob->getBlobPtr(),
			4, //header size
			$inputBlob->getBlobPtr(),
			$outputBlob->getBlobPtr()
		);

		return $outputBlob->getData();
	}

	public function decompressData($data){
		$inputBlob = LBLOB::fromData($data);

		$this->initialize();
		$ret = $this->getCompressedFileType($inputBlob);

		$dictionaryBlob = $this->loadDictionary();

		return $this->decompressBlob($dictionaryBlob, $inputBlob);
	}
}