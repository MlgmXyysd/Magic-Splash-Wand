<?php
/**
 *
 *    Copyright (C) 2002-2025 NekoYuzu (MlgmXyysd) All Rights Reserved.
 *    Copyright (C) 2013-2025 MeowCat Studio All Rights Reserved.
 *    Copyright (C) 2020-2025 Meow Mobile All Rights Reserved.
 *
 */

/**
 *
 * Magic Splash Logo! Wand (for OPlus Qualcomm devices)
 *
 * https://github.com/MlgmXyysd/Magic-Splash-Wand
 *
 * Tool for unpacking and packaging splash image for OPlus Qualcomm devices
 *
 * Environment requirement:
 *   - PHP 8.1.0+
 *   - GD Extension
 *
 * @author NekoYuzu (MlgmXyysd)
 * @version 1.1
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

$magic_header = "SPLASH LOGO!";

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
 * @date   2024/04/14 20:35:43
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
    logf("2. Supported formats are AVIF, BMP, GD2, GD, GIF,", "y", "*");
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
logf("* Magic Splash Logo! Wand                *", "g");
logf("*             for OPlus Qualcomm devices *", "g");
logf("* By NekoYuzu (MlgmXyysd)    Version 1.1 *", "g");
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

    $splash_contents = str_split(file_get_contents($argv[2]));

    $splash_padding = merge($splash_contents, 0, 16384);
    $splash_logo_header = str_split(merge($splash_contents, 16384, 16384));
    $splash_payload_data = str_split(merge($splash_contents, 32768));

    logf("Analyzing payload header...");

    $splash_magic = merge($splash_logo_header, 0, 12);
    if (strcmp($splash_magic, $magic_header) !== 0) {
        logf("Magic header mismatched, except $magic_header, but found $splash_magic", "r", "!", "e");
        exit();
    }

    logf("Magic header matched.");

    $descriptions = array(rtrim(merge($splash_logo_header, 12, 64)), rtrim(merge($splash_logo_header, 76, 64)), rtrim(merge($splash_logo_header, 140, 64)), rtrim(merge($splash_logo_header, 204, 64)));
    $splash_count = read_unsigned($splash_logo_header, 268);
    $splash_version = read_unsigned($splash_logo_header, 272);
    $splash_width = read_unsigned($splash_logo_header, 276);
    $splash_height = read_unsigned($splash_logo_header, 280);
    $splash_compress = read_unsigned($splash_logo_header, 284) === 1;

    if ($splash_count <= 0) {
        logf("Payload doesn't contain any images.", "r", "!", "e");
        exit();
    }

    logf("Splash description: " . json_encode($descriptions));
    logf("Splash version: $splash_version");
    logf("Splash size: " . $splash_width . "x" . $splash_height);
    logf("GZIP compression enabled: $splash_compress");
    logf("Found $splash_count images in payload.");

    $cursor = 288;
    $splash_array = array();
    $splash_names = array();

    for ($i = 0; $i < $splash_count; $i++) {
        $splash_offset = read_unsigned($splash_logo_header, $cursor);
        $cursor += 4;

        $splash_size = read_unsigned($splash_logo_header, $cursor);
        $cursor += 4;

        $splash_data = read_unsigned($splash_logo_header, $cursor);
        $cursor += 4;

        $splash_name = trim(merge($splash_logo_header, $cursor, 116));
        $cursor += 116;

        $splash_array[] = array($splash_offset, $splash_size, $splash_data, $splash_name);
        $splash_names[] = $splash_name;
    }

    $type = $splash_compress ? "compressed" : "raw";

    foreach ($splash_array as $data) {
        logf("Extracting $type splash " . $data[3] . "...");
        $payload = merge($splash_payload_data, $data[0], $data[2]);

        if ($splash_compress) {
            $payload = gzdecode($payload);
        }

        $size = strlen($payload);
        if ($size !== $data[1]) {
            logf("    Splash " . $data[3] . " size mismatched, except " . $data[1] . ", but found $size", "y", "-", "w");
        } else {
            logf("    Splash size matched.");
        }

        file_put_contents($output . DIRECTORY_SEPARATOR . $data[3] . ".bmp", $payload);
    }

    logf("Extracted " . count($splash_array) . " images.");

    logf("Writing out payload metadata...");

    $splash_description = array();

    foreach ($descriptions as $desc) {
        if (strcasecmp($desc, "") !== 0) {
            $splash_description[] = base64_encode($desc);
        }
    }

    $splash_metadata = array("c" => $splash_compress, "d" => $splash_description, "h" => $splash_height, "p" => base64_encode(gzencode($splash_padding)), "s" => $splash_names, "v" => $splash_version, "w" => $splash_width);

    file_put_contents($output . DIRECTORY_SEPARATOR . ".metadata", json_encode($splash_metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
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

    $metadata = $input . ".metadata";

    if (!is_readable($metadata) || !is_file($metadata)) {
        logf("Payload metadata file inaccessible!", "r", "!", "e");
        exit();
    }

    logf("Loading payload metadata...");

    $metadata = json_decode(file_get_contents($metadata), true);

    if (!isset($metadata["c"], $metadata["d"], $metadata["h"], $metadata["p"], $metadata["s"], $metadata["v"], $metadata["w"])) {
        logf("Invalid metadata format!", "r", "!", "e");
        exit();
    }

    logf("Metadata contains " . count($metadata["s"]) . " registered images.");

    logf("Processing input folder...");

    $dir = @opendir($argv[2]);

    if (!$dir) {
        logf("Can't open specified folder!", "r", "!", "e");
        exit();
    }

    $image_line = array();

    while (($file = readdir($dir)) !== false) {
        if (strcasecmp($file, ".") !== 0 && strcasecmp($file, "..") !== 0 && strcasecmp($file, ".metadata") !== 0) {
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

            $image_line[$file_info["filename"]] = array($file_info["extension"], $image_type);
        }
    }

    logf("Found " . count($image_line) . " images.");

    $valid_images = array();

    foreach ($metadata["s"] as $name) {
        if (isset($image_line[$name])) {
            $valid_images[] = array_merge(array($name), $image_line[$name]);
        }
    }

    logf("Totally " . count($valid_images) . " images will be added.");

    @closedir($dir);

    $splash_count = count($valid_images);

    $data_header = "";
    $data_payload = "";

    foreach ($valid_images as $data) {

        logf("Parsing " . $data[0] . "...");

        $file = $input . $data[0] . "." . $data[1];

        $image = match ($data[2]) {
            "AVIF" => imagecreatefromavif($file),
            "BMP" => true,
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

        if ($image === true) {
            $image = file_get_contents($file);
        } else {
            logf("    Converting to BMP format...");
            ob_start();
            imagebmp($image);
            imagedestroy($image);
            $image = ob_get_clean();
        }

        if ($metadata["c"]) {
            logf("    Compressing with GZIP...");

            $splash_output = gzencode($image, 9);
        } else {
            $splash_output = $image;
        }

        $data_header .= write_unsigned(strlen($data_payload)) . write_unsigned(strlen($image)) . write_unsigned(strlen($splash_output)) . str_pad(substr($data[0], 0, 116), 116, chr(0));
        $data_payload .= $splash_output;
    }

    logf("Constructing payload header...");

    $payload = gzdecode(base64_decode($metadata["p"])) . $magic_header;

    for ($i = 0; $i < count($metadata["d"]); $i++) {
        if ($i >= 4) {
            break;
        }
        $payload .= str_pad(substr(base64_decode($metadata["d"][$i]), 0, 64), 64, chr(0));
    }
    if (count($metadata["d"]) < 4) {
        $payload .= str_pad("", 64 * (4 - count($metadata["d"])), chr(0));
    }

    $payload .= write_unsigned($splash_count) . write_unsigned($metadata["v"]) . write_unsigned($metadata["w"]) . write_unsigned($metadata["h"]) . write_unsigned($metadata["c"] ? 1 : 0);

    logf("Merging payload...");

    $payload = str_pad($payload . $data_header, 32768, chr(0)) . $data_payload;

    logf("Writing out image...");

    file_put_contents($output, $payload);
} else {
    usage($argv[0]);
    logf("Unknown operation: " . $argv[1], "r", "!", "e");
    exit();
}

logf("All done, output is \"$output\".", "g");
