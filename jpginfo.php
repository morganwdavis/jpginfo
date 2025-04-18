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
 * Wikipedia - JPEG:    http://en.wikipedia.org/wiki/JPEG
 * JPEG File structure: http://www.xbdev.net/image_formats/jpeg/tut_jpg/jpeg_file_layout.php
 * Estimate Quality:    http://www.hackerfactor.com/src/jpegquality.c
 *
 * @param  string $imagePath Path to JPEG image
 * @return array|false       False on error, or
 *                           Associative array with keys: bits, height, width, progressive
 */

declare(strict_types=1);

// JPEG marker constants
const JPEG_SOI = "\xFF\xD8"; // Start Of Image
const JPEG_EOI = 0xD9;       // End Of Image (marker byte only)
const JPEG_SOF0 = 0xC0;      // Start Of Frame (Baseline DCT)
const JPEG_SOF2 = 0xC2;      // Start Of Frame (Progressive DCT)

// String containing marker bytes (excluding FF) that signal segment start
// Used for quick validation after reading 0xFF marker prefix.
const JPEG_MARKER_SEGMENTS =
    "\xC0" . // SOF0
    "\xC2" . // SOF2
    "\xC4" . // DHT
    "\xDB" . // DQT
    "\xDD" . // DRI
    "\xDA" . // SOS
    "\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7" . // RSTn
    "\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF" . // APPn
    "\xFE" . // COM
    "\xD9";  // EOI (though usually we stop before EOI)

/**
 * Parses a JPEG file to extract width, height, bits, and progressive flag.
 *
 * @param string $imagePath
 * @return array{width: int, height: int, bits: int, progressive: bool}|false
 */
function jpginfo(string $imagePath): array|false
{
    $fileHandle = @fopen($imagePath, 'rb');
    if ($fileHandle === false) {
        return false;
    }

    try {
        $soiMarker = fread($fileHandle, 2);
        if ($soiMarker === false || $soiMarker !== JPEG_SOI) {
            return false;
        }

        while (!feof($fileHandle)) {
            $markerBytes = fread($fileHandle, 2);
            if ($markerBytes === false || strlen($markerBytes) !== 2) {
                break;
            }

            if ($markerBytes[0] !== "\xFF") {
                break;
            }

            $markerType = $markerBytes[1];
            $markerTypeCode = ord($markerType);

            if ($markerTypeCode === JPEG_EOI) {
                break;
            }

            if (strpos(JPEG_MARKER_SEGMENTS, $markerType) === false) {
                break;
            }

            $lengthBytes = fread($fileHandle, 2);
            if ($lengthBytes === false || strlen($lengthBytes) !== 2) {
                break;
            }

            $segmentLength = unpack('n', $lengthBytes)[1];
            if ($segmentLength < 2) {
                break;
            }
            $dataLength = $segmentLength - 2;

            switch ($markerTypeCode) {
                case JPEG_SOF0:
                case JPEG_SOF2:
                    if ($dataLength < 5) {
                        break 2;
                    }

                    $sofData = fread($fileHandle, 5);
                    if ($sofData === false || strlen($sofData) !== 5) {
                        break 2;
                    }

                    $info = unpack('Cbits/nheight/nwidth', $sofData);
                    if ($info === false) {
                        break 2;
                    }

                    $info['progressive'] = ($markerTypeCode === JPEG_SOF2);
                    return $info;

                default:
                    if ($dataLength > 0) {
                        if (fseek($fileHandle, $dataLength, SEEK_CUR) === -1) {
                            break 2;
                        }
                    }
                    break;
            }
        }

        return false;
    } finally {
        if (is_resource($fileHandle)) {
            fclose($fileHandle);
        }
    }
}
