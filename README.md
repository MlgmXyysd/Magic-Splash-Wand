# Magic Splash Wand
![Version: 1.2](https://img.shields.io/badge/Version-1.2-brightgreen?style=for-the-badge) ![OPlus Version: 1.2](https://img.shields.io/badge/Version-OPlus%201.2-brightgreen?style=for-the-badge)

Magic Splash Wand is a set of scripts for unpacking and packaging the splash screen of Qualcomm series devices on boot, powered by PHP.

- Magic Splash!! Wand

Tool for unpacking and packaging splash image for Qualcomm devices.

- Magic Splash Logo! Wand

Tool for unpacking and packaging splash image for OPlus Qualcomm devices.

## How to use
1. Download and install PHP 8.1.0+ for your system from the [official website](https://www.php.net/downloads).
2. Enable GD extension in `php.ini`.
3. Open the terminal and use PHP interpreter to execute the [script](splash.php) or [script (for oplus)](splash_oplus.php) with the usage.
4. Wait for the script to run.

## Workaround
While cracking the BootLoader of OPPO Watch 2 eSIM Series, I got interested in its Splash. It's format is not quite the same as the [script provided by Qualcomm](https://git.codelinaro.org/clo/la/device/qcom/common/-/blob/LA.VENDOR.14.3.0.r1-11500-lanai.0/display/logo/logo_gen.py).

After analysis, it contains multiple logos and store information with the structure below:
```
Splash Payload (0x0)
│
├── Header [0x200] (0x0)
│   │
│   ├──(header structure) [0xC] (0x0)
│   │   │
│   │   │      *** Splash Identity ***
│   │   │
│   │   ├── char[0x8] magic;      <--- magic header, "SPLASH!!"
│   │   │
│   │   │      *** Content Identity ***
│   │   │
│   │   └── unsigned number;    <--- number of logos, little endian
│   │
│   ├──(logo structure) [0x14] (0xC)
│   │   │
│   │   │      *** Content 1 Information ***
│   │   │
│   │   ├── unsigned width;     <--- image's width, little endian
│   │   ├── unsigned height;    <--- image's height, little endian
│   │   ├── unsigned type;      <--- flag for compression, 0: Raw Image; 1: RLE24 Compressed Image
│   │   ├── unsigned blocks;    <--- block size, real size / 512
│   │   └── unsigned offset;    <--- offset of image's content, little endian
│   │
│   ├──(logo structure) [0x14] (0x20)
│   │   │
│   │   │      *** Content 2 Information ***
│   │   │
│   │   ├── unsigned width;
│   │   ├── unsigned height;
│   │   ├── unsigned type;
│   │   ├── unsigned blocks;
│   │   └── unsigned offset;
│   │
│   ├──(logo structure) [0x14] (0x34)
│   │   │
│   │   │      *** Content N Information ***
│   │   ├── ...
│   │   └── ...
│   │
│   └── ...
│
└── Payload data (0x200)
    │
    │      *** Content 1 data ***
    │
    ├── (data)     <--- image's content
    │
    │      *** Content 1 data ***
    │
    ├── (data)
    │
    │      *** Content N data ***
    │
    ├── ...
    └── ...
```
The original script was so old that it didn't even support Python 3.x. It's a pain to retrofit on top of it.

Out of distaste for Python syntax, I rewrote the script in PHP and added unpacking support.

-- Update --

Qualcomm devices from OPlus (OPPO/OnePlus/Realme) use a different but similar format to the above.

Combined with the Qualcomm structure, the analysis resulted in the following structure:
```
Splash Payload (0x0)
│
├── Padding [0x4000] (0x0)    <--- padding before the header, usually empty
│
├── Header [0x4000] (0x4000)
│   │
│   ├──(header structure) [0x120] (0x4000)
│   │   │
│   │   │      *** Splash Identity ***
│   │   │
│   │   ├── char[0xB] magic;     <--- magic header, "SPLASH LOGO!"
│   │   ├── char[0x40] desc1;    <--- description of this splash file (1)
│   │   ├── char[0x40] desc2;    <--- description of this splash file (2)
│   │   ├── char[0x40] desc3;    <--- description of this splash file (3)
│   │   ├── char[0x40] desc4;    <--- description of this splash file (4)
│   │   │
│   │   │      *** Content Identity ***
│   │   │
│   │   ├── unsigned number;      <--- number of logos, little endian
│   │   ├── unsigned version;     <--- version of this splash image, little endian, current 4
│   │   ├── unsigned width;       <--- screen width, little endian
│   │   ├── unsigned height;      <--- screen height, little endian
│   │   └── unsigned compress;    <--- flag for compression, 0: Raw Image; 1: GZIP Compressed Image
│   │
│   ├──(logo structure) [0x80] (0x4120)
│   │   │
│   │   │      *** Content 1 Information ***
│   │   │
│   │   ├── unsigned offset;       <--- offset of image's content, little endian
│   │   ├── unsigned real_size;    <--- content real size (decompressed if GZIP compressed)
│   │   ├── unsigned data_size;    <--- payload data size
│   │   └── char[0x74] name;       <--- content file name, extension is ".bmp"
│   │
│   ├──(logo structure) [0x80] (0x41A0)
│   │   │
│   │   │      *** Content 2 Information ***
│   │   │
│   │   ├── unsigned offset;
│   │   ├── unsigned real_size;
│   │   ├── unsigned data_size;
│   │   └── char[0x74] name;
│   │
│   ├──(logo structure) [0x80] (0x4220)
│   │   │
│   │   │      *** Content N Information ***
│   │   ├── ...
│   │   └── ...
│   │
│   └── ...
│
└── Payload data (0x8000)
    │
    │      *** Content 1 data ***
    │
    ├── (data)    <--- image's content
    │
    │      *** Content 1 data ***
    │
    ├── (data)
    │
    │      *** Content N data ***
    │
    ├── ...
    └── ...
```

Compression format was changed from RLE24 to GZIP (magic number `1F 8B 08 00`). Device is confirmed to be able to load content at the maximum compression level.

Have fun :)

## TO-DOs
- Magic Splash!! Wand
	- [ ] Add supports for Raw package method
	- [x] ~~Add supports for AVIF, GD2, GD, TGA, WBMP, WEBP, XBM, XPM, etc. image formats~~
- Magic Splash Logo! Wand
	- [x] ~~Add supports for AVIF, GD2, GD, GIF, JPEG, PNG, TGA, WBMP, WEBP, XBM, XPM, etc. image formats~~

## Changelog
- Magic Splash!! Wand
	- v1.2:
		- Add supports for AVIF, GD2, GD, TGA, WBMP, WEBP, XBM, XPM, etc. image formats.
		- Optimized code logic.
		- Upgraded PHP requirement to 8.1.0.
	- v1.1:
		- Implemented repack feature.
	- v1.0:
		- Support splash screen format Qualcomm devices
- Magic Splash Logo! Wand
	- v1.2:
		- Optimized log output.
	- v1.1:
		- Add supports for AVIF, GD2, GD, GIF, JPEG, PNG, TGA, WBMP, WEBP, XBM, XPM, etc. image formats.
	- v1.0:
		- Support splash screen format for OPlus (OPPO/OnePlus/Realme) Qualcomm devices

## License
No license, you are only allowed to use this project. All rights are reserved by [MeowCat Studio](https://github.com/MeowCat-Studio), [Meow Mobile](https://github.com/Meow-Mobile) and [NekoYuzu (MlgmXyysd)](https://github.com/MlgmXyysd).
