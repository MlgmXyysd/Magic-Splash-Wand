<?php
/**
 * 
 *    Copyright (C) 2002-2022 MlgmXyysd All Rights Reserved.
 *    Copyright (C) 2013-2022 MeowCat Studio All Rights Reserved.
 *    Copyright (C) 2020-2022 Meow Mobile All Rights Reserved.
 * 
 */

/**
 * 
 * Magic Splash!! Wand
 * 
 * Unpacking and packaging for Qualcomm splash images
 * 
 * Environment requirement:
 *   - PHP + GD Extension
 * 
 * TODO:
 *   - Package method: Raw
 *   - Image format support: GD, GD2, WBMP, WEBP, XBM, XPM
 * 
 * @author MlgmXyysd
 * @version 1.1
 * 
 * All copyright in the software is not allowed to be deleted
 * or changed without permission.
 * 
 */

$magic_header = "SPLASH!!";
$block_size = 512;
$ver = "1.1";

function merge($array, $start = 0, $end = -1) {
	if ($end == -1) {
		$end = count($array) - 1;
	}
	$result = "";
	for ($i = $start; $i <= $end ;$i++) {
		if (count($array) <= $i) {
			$result .= chr(00);
		} else {
			$result .= $array[$i];
		}
	}
	return $result;
}

function write_unsigned($data) {
	return hex2bin(merge(array_reverse(str_split(sprintf("%08s", dechex($data)), 2))));
}

function read_unsigned($payload, $offset) {
	return hexdec(merge(array_reverse(str_split(bin2hex(merge($payload, $offset, $offset + 3)), 2))));
}

function usage($myself) {
	echo("- \033[33mUsage: \033[0m\n");
	echo("-   \033[33m" . $myself . " unpack <splash_img> [output_dir]\033[0m\n");
	echo("-   \033[33m    Unpack a splash image\033[0m\n");
	echo("-   \033[33m" . $myself . " repack <splash_dir> [output_img]\033[0m\n");
	echo("-   \033[33m    Repack a splash image\033[0m\n");
	echo("- \033[33mNotes:\033[0m\n");
	echo("-   \033[33m1. The default output dir is \"output\" and the\033[0m\n");
	echo("-   \033[33m   default output image is \"splash.img\"\033[0m\n");
	echo("-   \033[33m2. Image order is the dictionary order of file\033[0m\n");
	echo("-   \033[33m   names\033[0m\n");
	echo("-   \033[33m3. Image can be a PNG, JPEG, BMP, GIF file. But\033[0m\n");
	echo("-   \033[33m   if it is a GIF, only get the first frame\033[0m\n");
}

echo("\033[32m***********************\033[0m\n");
echo("\033[32m* Magic Splash!! Wand *\033[0m\n");
echo("\033[32m* By MlgmXyysd        *\033[0m\n");
echo("\033[32m***********************\033[0m\n");
echo("- Version " . $ver . "\n");

if ($argc < 2) {
	usage($argv[0]);
	exit();
}

if ($argv[1] === "unpack") {
	
	echo("- Operation: Unpack splash image\n");
	
	if ($argc < 3) {
		exit("! \033[31mError: No image specified\033[0m\n");
	}
	
	if (!is_readable($argv[2]) || !is_file($argv[2])) {
		exit("! \033[31mError: The specified image is inaccessible\033[0m\n");
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
		exit("! \033[31mError: The output dir is inaccessible\033[0m\n");
	}
	
	echo("- Loading splash img...\n");

	$splash_content = file_get_contents($argv[2]);
	$splash_contents = str_split($splash_content);

	$splash_logo_header = str_split(merge($splash_contents, 0, $block_size - 1));
	$splash_payload_data = str_split(merge($splash_contents, $block_size, count($splash_contents) - 1));
	
	if (merge($splash_logo_header, 0, 7) !== $magic_header) {
		exit("! \033[31mError: Magic header mismatch\033[0m\n");
	} else {
		echo("- Magic header matched\n");
	}
	$splash_count = read_unsigned($splash_logo_header, 8);
	echo("- Found " . $splash_count . " splashes\n");
	
	$cursor_offset = 12;
	$splash_array = array();
	
	for ($i = 0; $i < $splash_count; $i++) {
		$splash_width = read_unsigned($splash_logo_header, $cursor_offset);
		$cursor_offset += 4;
		
		$splash_height = read_unsigned($splash_logo_header, $cursor_offset);
		$cursor_offset += 4;
		
		$splash_type = read_unsigned($splash_logo_header, $cursor_offset);
		$cursor_offset += 4;
		
		$splash_size = read_unsigned($splash_logo_header, $cursor_offset) * $block_size;
		$cursor_offset += 4;
		
		$splash_offset = read_unsigned($splash_logo_header, $cursor_offset) - $block_size;
		$cursor_offset += 4;
		
		$splash_array[] = array("i" => $i, "w" => $splash_width, "h" => $splash_height, "t" => $splash_type, "s" => $splash_size, "o" => $splash_offset);
	}

	foreach ($splash_array as $data) {
		$type = $data["t"] === 1 ? "compressed" : "raw";
		
		echo("- Extracting " . $type . " splash " . $data["i"] . "...\n");
		$image = imagecreatetruecolor($data["w"], $data["h"]);
		
		$area = 0;
		$size = $data["w"] * $data["h"];
		$payload = str_split(merge($splash_payload_data, $data["o"], $data["o"] + $data["s"] - 1));
		
		if ($data["t"] === 1) {
			$pos = 0;
			$x = 0;
			$y = 0;
			
			while ($area <= $size) {
				$count = unpack("C", merge($payload, $pos, $pos + 1))[1];
				$pos++;
				$repeat = $count & 0x80;
				$count = ($count & 0x7f) + 1;
				
				for ($i = 0; $i < $count; $i++) {
					$red = unpack("C", merge($payload, $pos + 2, $pos + 3))[1];
					$green = unpack("C", merge($payload, $pos + 1, $pos + 2))[1];
					$blue = unpack("C", merge($payload, $pos, $pos + 1))[1];
					
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
				
				if ($x === $data["w"]) {
					$y++;
					$x = 0;
				}
			}
		} else {
			while ($area <= $size) {				
				imagesetpixel($image, $area % $data["w"], $area / $data["w"], imagecolorallocate($image, ord($payload[$area * 3 + 2]), ord($payload[$area * 3 + 1]), ord($payload[$area * 3])));
				
				$area++;
			}
		}
		
		imagepng($image, $output . DIRECTORY_SEPARATOR . $data["i"] . ".png");
	}
	
} else if ($argv[1] === "repack") {
	
	echo("- Operation: Repack splash image\n");
	
	if ($argc < 3) {
		exit("! \033[31mError: No folder specified\033[0m\n");
	}
	
	if (!is_readable($argv[2]) || !is_dir($argv[2])) {
		exit("! \033[31mError: The specified folder is inaccessible\033[0m\n");
	}
	
	$input = $argv[2] . DIRECTORY_SEPARATOR;
	
	if ($argc > 3) {
		$output = $argv[3];
	} else {
		$output = "splash.img";
	}
	
	echo("- Loading images...\n");
	
	$dir = @opendir($argv[2]);
	
	if (!$dir) {
		exit("! \033[31mError: Can't open specified folder\033[0m\n");
	}
	
	$image_line = array();
	
	$count = 0;
	
	while (($file = readdir($dir)) !== false) {
		if ($file !== "." && $file !== "..") {
			$info = @getimagesize($input . $file);
			
			$image_type = false;
			
			if ($info) {
				switch ($info[2]) {
					case 1:
						$image_type = "GIF";
						break;
					case 2:
						$image_type = "JPG";
						break;
					case 3:
						$image_type = "PNG";
						break;
					case 6:
						$image_type = "BMP";
						break;
					default:
						$image_type = false;
				}
			}
			
			if ($image_type) {
				$image_line[] = array("c" => $count, "f" => $file, "i" => $image_type);
				$count++;
			}
		}
	}
	
	echo("- Found " . $count . " images\n");

	@closedir($dir);
	
	$splash_image = "";
	$data_header = "";
	$payload = "";
	$offset_count = $block_size;
	$splash_count = 0;
	
	foreach ($image_line as $data) {
		
		echo("- Parsing " . $data["f"] . "...\n");
		
		switch ($data["i"]) {
			case "GIF":
				$image = imagecreatefromgif($input . $data["f"]);
				break;
			case "JPG":
				$image = imagecreatefromjpeg($input . $data["f"]);
				break;
			case "PNG":
				$image = imagecreatefrompng($input . $data["f"]);
				break;
			case "BMP":
				$image = imagecreatefrombmp($input . $data["f"]);
				break;
			default:
				$image = false;
		}
		
		if (!$image) {
			echo("- \033[33mCan't open image, skipping...\033[0m\n");
			continue;
		}
		
		if (!imageistruecolor($image)) {
			echo("- \033[33mImage format unsupported, skipping...\033[0m\n");
			continue;
		}
		
		$width = imagesx($image);
		$height = imagesy($image);
		
		$x = 0;
		$y = 0;
		
		$splash_output = "";
		
		echo("- Compressing with RLE24...\n");
		
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
		
		$data_header .= write_unsigned($width) . write_unsigned($height) . write_unsigned(1) . write_unsigned($splash_block) . write_unsigned($offset_count);
		$splash_output = merge(str_split($splash_output), 0, $splash_size - 1);
		$payload .= $splash_output;
		$splash_count++;
		$offset_count += $splash_size;
	}
	
	echo("- Generating magic header...\n");
	
	$splash_image = merge(str_split($magic_header . write_unsigned($splash_count) . $data_header), 0, $block_size - 1) . $payload;
	
	echo("- Writing out image...\n");
	
	file_put_contents($output, $splash_image);
	
} else {
	usage($argv[0]);
	exit("! \033[31mError: Unknown operation: " . $argv[1] . "\033[0m\n");
}
echo("- All done, output is \"" . $output . "\"\n");
?>
