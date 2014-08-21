<?php
/**
 * jpginfo.php
 * 
 * Efficiently parses JPEG stream by reading minimal data in order
 * to return size and optimization info quickly.
 * 
 * https://github.com/morganwdavis/jpginfo
 * Copyright (C) 2014 Morgan Davis; Licensed MIT
 *
 * See:
 *
 * Wikipedia - JPEG: 	http://en.wikipedia.org/wiki/JPEG
 * JPEG File structure:	http://www.xbdev.net/image_formats/jpeg/tut_jpg/jpeg_file_layout.php
 * Estimate Quality:	http://www.hackerfactor.com/src/jpegquality.c
 * 
 * @param  string $image Path to JPEG image
 * @return mixed          False on error, or
 *                        Associative array with keys: bits, height, width, progressive
 */
function jpginfo($image) {

	$markers = 	"\xD8" . //	SOI - Start Of Image	
				"\xC0" . // SOF0 - Start Of Frame (Baseline DCT)
				"\xC2" . // SOF2 - Start Of Frame (Progressive DCT)
				"\xC4" . // DHT	- Define Huffman Table(s)
				"\xDB" . // DQT - Define Quantization Table(s)
				"\xDD" . // DRI - Define Restart Interval
				"\xDA" . // SOS - Start Of Scan
				"\xD0" . // RSTn (n=0..7) - Restart
						"\xD1\xD2\xD3\xD4\xD5\xD6\xD7" .
				"\xE0" . // APPn (n=0..F) - Application-specific
						"\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF" .
				"\xFE" . // COM - Comment: Contains a text comment
				"\xD9";  // EOI	- End of Image

	if (($fh = fopen($image, 'rb')) && ($rec = fread($fh, 2)) && ($rec == "\xFF\xD8")) {
		while (!feof($fh) && ($rec = fread($fh, 4))) {
			$type = $rec[1];
			if (($rec[0] == "\xFF") && (strpos($markers, $type) !== false)) {
				$size = unpack('nlen', $rec[2] . $rec[3]);
				$size = $size['len'];
				switch ($type) {
					case "\xC0": // SOF0 -- baseline
					case "\xC2": // SOF2 -- progressive
						$rec = fread($fh, 5);
						$info = unpack('Cbits/nheight/nwidth', $rec);
						$info['progressive'] = ($type == "\xC2");
						fclose($fh);
						return $info;				
				}
				fseek($fh, $size - 2, SEEK_CUR);
			} else {
				break;
			}
		}
	}
	fclose($fh);
	return false;
}

?>
