<?php
/**
 *
 *    Copyright (C) 2002-2025 MlgmXyysd All Rights Reserved.
 *    Copyright (C) 2013-2025 MeowCat Studio All Rights Reserved.
 *    Copyright (C) 2020-2025 Meow Mobile All Rights Reserved.
 *
 */

/**
 *
 * Magic Splash!! Wand
 *
 * https://github.com/MlgmXyysd/Magic-Splash-Wand
 *
 * Tool for unpacking and packaging splash image for Qualcomm devices
 *
 * Environment requirement:
 *   - PHP 8.1.0+
 *   - GD Extension
 *
 * TODO:
 *   - Package method: Raw
 *
 * @author MlgmXyysd
 * @version 1.2
 *
 * All copyright in the software is not allowed to be deleted
 * or changed without permission.
 *
 */

/***************************************
 *               WARNING               *
 *    Do NOT modify the codes below    *
 *               WARNING               *
 ***************************************/

/*************************
 *    Constants Start    *
 *************************/

$magic_header = "SPLASH!!";
$block_size = 512;

/***********************
 *    Constants End    *
 ***********************/

/*************************
 *    Functions Start    *
 *************************/

/**
 * Formatted log
 * @param  $m  string  optional  Message
 * @param  $c  string  optional  Color
 * @param  $p  string  optional  Indicator
 * @param  $t  string  optional  Type (Level)
 * @author NekoYuzu (MlgmXyysd)
 * @date   2024/04/14 14:00:03
 */

function logf(string $m = "", string $c = "", string $p = "-", string $t = "I"): void
{
    $c = match (strtoupper($c)) {
        "R" => "\033[31m",
        "G" => "\033[32m",
        "Y" => "\033[33m",
        default => "",
    };
    $t = match (strtoupper($t)) {
        "W" => "WARN",
        "E" => "ERROR",
        default => "INFO",
    };
    print(date("[Y-m-d] [H:i:s]") . " [" . $t . "] " . $p . " " . $c . $m . "\033[0m" . PHP_EOL);
}

/**
 * Merge array slice to string with specified offset & size
 * @param  $payload  array   required  Array slices
 * @param  $offset   int     optional  Offset
 * @param  $size     int     optional  Expected size
 * @return           string            Merged string
 * @author NekoYuzu (MlgmXyysd)
 * @date   2024/04/14 14:02:35
 */

function merge(array $payload, int $offset = 0, int $size = 0): string
{
    $end = $offset + $size - 1;
    if ($size === 0) {
        $end = count($payload) - 1;
    }
    $result = "";
    for ($i = $offset; $i <= $end; $i++) {
        if (count($payload) <= $i) {
            $result .= chr(0);
        } else {
            $result .= $payload[$i];
        }
    }
    return $result;
}

/**
 * Transforms integer to little-endian unsigned format
 * @param  $data  string|int  required  Data
 * @return        string                Result
 * @author NekoYuzu (MlgmXyysd)
 * @date   2021/10/12 13:02:38
 */

function write_unsigned(string|int $data): string
{
    return hex2bin(merge(array_reverse(str_split(sprintf("%08s", dechex((int)$data)), 2))));
}

/**
 * Read a little-endian unsigned integer from the specified array slices & offset
 * @param  $payload  array  required  Array slices
 * @param  $offset   int    required  Offset
 * @return           int              Result
 * @author NekoYuzu (MlgmXyysd)
 * @date   2021/10/12 09:32:50
 */

function read_unsigned(array $payload, int $offset): int
{
    return hexdec(merge(array_reverse(str_split(bin2hex(merge($payload, $offset, 4)), 2))));
}

/**
 * Print usage
 * @param  $self  string  required  Self name
 * @author NekoYuzu (MlgmXyysd)
 * @date   2024/04/14 21:48:01
 */

function usage(string $self): void
{
    logf("Usage:", "y", "*");
    logf($self . " unpack <splash_img> [output_dir]", "y", "*");
    logf("    Unpack a splash image", "y", "*");
    logf($self . " repack <splash_dir> [output_img]", "y", "*");
    logf("    Repack a splash image", "y", "*");
    logf("Notes:", "y", "*");
    logf("1. The default output dir is \"output\" and the", "y", "*");
    logf("   default output image is \"splash.img\";", "y", "*");
    logf("2. Image order is the dictionary order of file", "y", "*");
    logf("   names;", "y", "*");
    logf("3. Supported formats are AVIF, BMP, GD2, GD, GIF,", "y", "*");
    logf("   JPEG, PNG, TGA, WBMP, WEBP, XBM, XPM. But if", "y", "*");
    logf("   it is a GIF, only get the first frame.", "y", "*");
}

/***********************
 *    Functions End    *
 ***********************/

/**********************
 *    Banner Start    *
 **********************/

logf("******************************************", "g");
logf("*          Magic Splash!! Wand           *", "g");
logf("* By NekoYuzu (MlgmXyysd)    Version 1.2 *", "g");
logf("******************************************", "g");
logf("GitHub: https://github.com/MlgmXyysd");
logf("XDA: https://xdaforums.com/m/mlgmxyysd.8430637");
logf("X (Twitter): https://x.com/realMlgmXyysd");
logf("PayPal: https://paypal.me/MlgmXyysd");
logf("My Blog: https://www.neko.ink/");
logf("******************************************", "g");

/********************
 *    Banner End    *
 ********************/

/********************
 *    Main Logic    *
 ********************/

if ($argc < 2) {
    usage($argv[0]);
    exit();
}

ini_set("memory_limit", "-1");

if (strcasecmp($argv[1], "unpack") === 0) {
    logf("Operation: Unpack splash image", "y");

    if ($argc < 3) {
        logf("No payload specified!", "r", "!", "e");
        usage($argv[0]);
        exit();
    }

    if (!is_readable($argv[2]) || !is_file($argv[2])) {
        logf("Specified payload is inaccessible!", "r", "!", "e");
        exit();
    }

    if ($argc > 3) {
        $output = $argv[3];
    } else {
        $output = "output";
    }

    if (!file_exists($output)) {
        @mkdir($output, 0777, true);
    }

    if (!is_writable($output)) {
        logf("Output dir is inaccessible!", "r", "!", "e");
        exit();
    }

    logf("Loading splash payload...");

    $splash_content = file_get_contents($argv[2]);
    $splash_contents = str_split($splash_content);

    $splash_logo_header = str_split(merge($splash_contents, 0, $block_size));
    $splash_payload_data = str_split(merge($splash_contents, $block_size));

    $splash_magic = merge($splash_logo_header, 0, 8);
    if (strcmp($splash_magic, $magic_header) !== 0) {
        logf("Magic header mismatched, except $magic_header, but found $splash_magic", "r", "!", "e");
        exit();
    }

    logf("Magic header matched.");

    $splash_count = read_unsigned($splash_logo_header, 8);

    if ($splash_count <= 0) {
        logf("Payload doesn't contain any images.", "r", "!", "e");
        exit();
    }

    logf("Found $splash_count images in payload.");

    $cursor = 12;
    $splash_array = array();

    for ($i = 0; $i < $splash_count; $i++) {
        $splash_width = read_unsigned($splash_logo_header, $cursor);
        $cursor += 4;

        $splash_height = read_unsigned($splash_logo_header, $cursor);
        $cursor += 4;

        $splash_type = read_unsigned($splash_logo_header, $cursor);
        $cursor += 4;

        $splash_size = read_unsigned($splash_logo_header, $cursor) * $block_size;
        $cursor += 4;

        $splash_offset = read_unsigned($splash_logo_header, $cursor) - $block_size;
        $cursor += 4;

        $splash_array[] = array($i, $splash_width, $splash_height, $splash_type, $splash_size, $splash_offset);
    }

    foreach ($splash_array as $data) {
        $type = $data[3] === 1 ? "compressed" : "raw";

        logf("Extracting $type splash " . $data[0] . "...");

        $image = imagecreatetruecolor($data[1], $data[2]);

        $area = 0;
        $size = $data[1] * $data[2];
        $payload = str_split(merge($splash_payload_data, $data[5], $data[4]));

        if (strcasecmp($type, "compressed") === 0) {
            $pos = 0;
            $x = 0;
            $y = 0;

            while ($area <= $size) {
                $count = unpack("C", merge($payload, $pos, 2))[1];
                $pos++;
                $repeat = $count & 128;
                $count = ($count & 127) + 1;

                for ($i = 0; $i < $count; $i++) {
                    $red = unpack("C", merge($payload, $pos + 2, 1))[1];
                    $green = unpack("C", merge($payload, $pos + 1, 1))[1];
                    $blue = unpack("C", merge($payload, $pos, 1))[1];

                    imagesetpixel($image, $x, $y, imagecolorallocate($image, $red, $green, $blue));

                    $area++;
                    $x++;

                    if (!$repeat) {
                        $pos += 3;
                    }
                }

                if ($repeat) {
                    $pos += 3;
                }

                if ($x === $data[1]) {
                    $y++;
                    $x = 0;
                }
            }
        } else {
            while ($area <= $size) {
                imagesetpixel($image, $area % $data[1], $area / $data[1], imagecolorallocate($image, ord($payload[$area * 3 + 2]), ord($payload[$area * 3 + 1]), ord($payload[$area * 3])));

                $area++;
            }
        }

        imagepng($image, $output . DIRECTORY_SEPARATOR . $data[0] . ".png");
    }
} else if (strcasecmp($argv[1], "repack") === 0) {
    logf("Operation: Repack splash image", "y");

    if ($argc < 3) {
        logf("No folder specified!", "r", "!", "e");
        usage($argv[0]);
        exit();
    }

    if (!is_readable($argv[2]) || !is_dir($argv[2])) {
        logf("Specified folder is inaccessible!", "r", "!", "e");
        exit();
    }

    $input = $argv[2];

    if (!str_ends_with($input, DIRECTORY_SEPARATOR)) {
        $input .= DIRECTORY_SEPARATOR;
    }

    if ($argc > 3) {
        $output = $argv[3];
    } else {
        $output = "splash.img";
    }

    if (file_exists($output) && (!is_writable($output) || !is_file($output))) {
        logf("Output file is inaccessible!", "r", "!", "e");
        exit();
    }

    logf("Processing input folder...");

    $dir = @opendir($argv[2]);

    if (!$dir) {
        logf("Can't open specified folder!", "r", "!", "e");
        exit();
    }

    $image_line = array();

    while (($file = readdir($dir)) !== false) {
        if (strcasecmp($file, ".") !== 0 && strcasecmp($file, "..") !== 0) {
            $file = $input . $file;

            $file_info = pathinfo($file);
            $image_info = @getimagesize($file);

            $image_type = false;

            if ($image_info) {
                $image_type = match ($image_info[2]) {
                    IMAGETYPE_GIF => "GIF",
                    IMAGETYPE_JPEG, IMAGETYPE_JPEG2000 => "JPEG",
                    IMAGETYPE_PNG => "PNG",
                    IMAGETYPE_BMP => "BMP",
                    IMAGETYPE_WBMP => "WBMP",
                    IMAGETYPE_XBM => "XBM",
                    IMAGETYPE_WEBP => "WEBP",
                    IMAGETYPE_AVIF => "AVIF",
                    default => strtoupper($file_info["extension"])
                };
            }

            if (isset($image_line[$file_info["filename"]]) && strcasecmp($image_line[$file_info["filename"]][1], "BMP") === 0) {
                continue;
            }

            $image_line[] = array($file_info["filename"], $file_info["extension"], $image_type);
        }
    }

    logf("Found " . count($image_line) . " images.");

    @closedir($dir);

    $splash_count = count($image_line);

    $data_header = "";
    $data_payload = "";

    foreach ($image_line as $data) {

        logf("Parsing " . $data[0] . "...");

        $file = $input . $data[0] . "." . $data[1];

        $image = match ($data[2]) {
            "AVIF" => imagecreatefromavif($file),
            "BMP" => imagecreatefrombmp($file),
            "GD2" => imagecreatefromgd2($file),
            "GD" => imagecreatefromgd($file),
            "GIF" => imagecreatefromgif($file),
            "JPEG" => imagecreatefromjpeg($file),
            "PNG" => imagecreatefrompng($file),
            "TGA" => imagecreatefromtga($file),
            "WBMP" => imagecreatefromwbmp($file),
            "WEBP" => imagecreatefromwebp($file),
            "XBM" => imagecreatefromxbm($file),
            "XPM" => imagecreatefromxpm($file),
            default => imagecreatefromstring(file_get_contents($file))
        };

        if (!$image) {
            logf("    Can't open image or image not supported, skipping...", "y", "-", "w");
            $splash_count--;
            continue;
        }

        if (!imageistruecolor($image)) {
            logf("    Image color format is not supported, skipping...", "y", "-", "w");
            $splash_count--;
            continue;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $splash_output = "";

        logf("    Compressing with RLE24...");

        for ($y = 0; $y < $height; $y++) {

            $line = array();
            $list = array();
            $line_run = array();

            $line_count = 0;
            $line_repeat = -1;

            for ($x = 0; $x < $width; $x++) {
                $line[] = ImageColorAt($image, $x, $y);
            }

            for ($i = 0; $i < count($line) - 1; $i++) {
                if ($line[$i] !== $line[$i + 1]) {
                    $line_run[] = $line[$i];
                    $line_count++;
                    if ($line_repeat === 1) {
                        $list[] = array($line_count + 128, $line_run);
                        $line_count = 0;
                        $line_run = array();
                        $line_repeat = -1;
                        if ($i === count($line) - 2) {
                            $line_run = array($line[$i + 1]);
                            $list[] = array(1, $line_run);
                        }
                    } else {
                        $line_repeat = 0;

                        if ($line_count === 128) {
                            $list[] = array(128, $line_run);
                            $line_count = 0;
                            $line_run = array();
                            $line_repeat = -1;
                        }

                        if ($i === count($line) - 2) {
                            $line_run[] = $line[$i + 1];
                            $list[] = array($line_count + 1, $line_run);
                        }
                    }
                } else {
                    if ($line_repeat === 0) {
                        $list[] = array($line_count, $line_run);
                        $line_count = 0;
                        $line_run = array();
                        $line_repeat = -1;
                        if ($i === count($line) - 2) {
                            $line_run[] = $line[$i + 1];
                            $line_run[] = $line[$i + 1];
                            $list[] = array(130, $line_run);
                            break;
                        }
                    }
                    $line_run[] = $line[$i];
                    $line_repeat = 1;
                    $line_count++;
                    if ($line_count === 128) {
                        $list[] = array(256, $line_run);
                        $line_count = 0;
                        $line_run = array();
                        $line_repeat = -1;
                    }
                    if ($i === count($line) - 2) {
                        if ($line_count === 0) {
                            $line_run = array($line[$i + 1]);
                            $list[] = array(1, $line_run);
                        } else {
                            $line_run[] = $line[$i];
                            $list[] = array($line_count + 129, $line_run);
                        }
                    }
                }
            }

            foreach ($list as $result) {
                $splash_output .= pack("C", $result[0] - 1);
                if ($result[0] > 128) {
                    $splash_output .= pack("C", $result[1][0] & 0xFF);
                    $splash_output .= pack("C", ($result[1][0]) >> 8 & 0xFF);
                    $splash_output .= pack("C", ($result[1][0]) >> 16 & 0xFF);
                } else {
                    foreach ($result[1] as $pixel) {
                        $splash_output .= pack("C", $pixel & 0xFF);
                        $splash_output .= pack("C", ($pixel >> 8) & 0xFF);
                        $splash_output .= pack("C", ($pixel >> 16) & 0xFF);
                    }
                }
            }
        }

        $splash_block = ceil(strlen($splash_output) / $block_size);
        $splash_size = $splash_block * $block_size;

        $data_header .= write_unsigned($width) . write_unsigned($height) . write_unsigned(1) . write_unsigned($splash_block) . write_unsigned(strlen($data_payload) + $block_size);
        $data_payload .= merge(str_split($splash_output), 0, $splash_size);
    }

    logf("Constructing payload header...");

    $payload = merge(str_split($magic_header . write_unsigned($splash_count) . $data_header), 0, $block_size) . $data_payload;

    logf("Writing out image...");

    file_put_contents($output, $payload);
} else {
    usage($argv[0]);
    logf("Unknown operation: " . $argv[1], "r", "!", "e");
    exit();
}

logf("All done, output is \"$output\".", "g");
