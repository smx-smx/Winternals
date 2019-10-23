/**
  * Copyright(C) 2019 Stefano Moioli <smxdev4@gmail.com>
  */ 
#include <iostream>
#include <fstream>
#include <vector>
#include <Windows.h>
#include "Wcp.h"

int deltaDecompress(const char* filename) {
	std::ifstream ifs(filename, std::ios::binary);
	std::vector<unsigned char> buffer(std::istreambuf_iterator<char>(ifs), {});

	int ret;

	LBLOB inData = LBLOB(buffer.size(), buffer.data());
	unsigned int ftyp = Windows::WCP::Rtl::GetCompressedFileType(&inData);

	ret = Windows::Rtl::InitializeDeltaCompressor(nullptr);


	LBLOB dictionary;
	HINSTANCE hWcp = LoadLibraryA("wcp.dll");
	ret = Windows::Rtl::LoadFirstResourceLanguageAgnostic(
		0,
		hWcp,
		reinterpret_cast<const unsigned short*>(0x266),
		reinterpret_cast<const unsigned short*>(0x1),
		&dictionary
	);

	if (ftyp == CompressedFileType::LZMS) {
		throw "LZMS - TODO";
	}

	Windows::Rtl::AutoDeltaBlob outData;
	ret = Windows::Rtl::DeltaDecompressBuffer(
		2,
		&dictionary,
		MANIFEST_HEADER_SIZE,
		&inData,
		&outData
	);

	std::cout << outData.pData;
	return 0;
}

int main(int argc, char *argv[])
{
	if (argc < 2) {
		std::cerr << "Usage: " << argv[0] << " [manifest]";
		return 1;
	}
	const char* filename = argv[1];
	deltaDecompress(filename);
	return 0;
}
