# Magic Splash Wand
![Version: 1.1](https://img.shields.io/badge/Version-1.1-brightgreen?style=for-the-badge)

Magic Splash!! Wand

Unpacking and packaging for Qualcomm splash images.

## How to use
1. Download and install PHP for your system from the [official website](https://www.php.net/downloads).
2. Enable GD extension in `php.ini`.
3. Open the terminal and use PHP interpreter to execute the [script](splash.php) with the usage.
4. Wait for the script to run.

## Changelog
- v1.1:
    - Implement repack
- v1.0:
    - First ver

## TO-DOs
- [ ] Package method: Raw
- [ ] Image format support: GD, GD2, WBMP, WEBP, XBM, XPM

## Workaround
While cracking the BootLoader of OPPO Watch 2 eSIM Series, I got interested in its Splash. It's format is not quite the same as the [script provided by Qualcomm](https://source.codeaurora.org/quic/la/device/qcom/common/tree/display/logo/logo_gen.py?h=LA.UM.9.6.2.c25).

After analysis, it contains multiple logos and store information with the structure below:
```
Splash
├── Header
│   ├──(header structure)
│   │   │
│   │   │      *** Splash Identity ***
│   │   │
│   │   ├── char[8] magic;      <--- magic header, "SPLASH!!"
│   │   │
│   │   │      *** Content Identity ***
│   │   │
│   │   └── unsigned number;    <--- number of logos, little endian
│   │
│   ├──(logo structure)
│   │   │
│   │   │      *** Content 1 Information ***
│   │   │
│   │   ├── unsigned width;     <--- logo's width, little endian
│   │   ├── unsigned height;    <--- logo's height, little endian
│   │   ├── unsigned type;      <--- 0, Raw Image; 1, RLE24 Compressed Image
│   │   ├── unsigned blocks;    <--- block number, real size / 512
│   │   └── unsigned offset;    <--- offset of logo's content, little endian
│   │
│   ├──(logo structure)
│   │   │
│   │   │      *** Content 2 Information ***
│   │   │
│   │   ├── unsigned width;
│   │   ├── unsigned height;
│   │   ├── unsigned type;
│   │   ├── unsigned blocks;
│   │   └── unsigned offset;
│   │
│   ├──(logo structure)
│   │   │
│   │   │      *** Content N Information ***
│   │   ├── ...
│   │   └── ...
│   │
│   └── ...
│
└── Payload data
    │
    │      *** Content 1 data ***
    │
    ├── (data)     <--- logo's content
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

Have fun :)

## License
No license, you are only allowed to use this project. All rights are reserved by [MeowCat Studio](https://github.com/MeowCat-Studio), [Meow Mobile](https://github.com/Meow-Mobile) and [MlgmXyysd](https://github.com/MlgmXyysd).
