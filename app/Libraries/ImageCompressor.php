<?php

namespace App\Libraries;

/**
 * Server-side image compressor using GD (resizes >1600px and re-encodes at ~78% quality
 * when the compressed output is smaller than the original).
 */
class ImageCompressor
{
    public static function compress(string $filePath, int $maxDim = 1600, int $quality = 78): void
    {
        if (!is_file($filePath) || !extension_loaded('gd')) {
            return;
        }
        $info = @getimagesize($filePath);
        if (!$info || empty($info[0]) || empty($info[1])) {
            return;
        }
        [$w, $h, $type] = $info;
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG  => @imagecreatefrompng($filePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : false,
            default        => false,
        };
        if (!$src) {
            return;
        }

        // Fix EXIF orientation on JPEGs if available
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($filePath);
            $ori  = (int) ($exif['Orientation'] ?? 1);
            if ($ori === 3) {
                $src = imagerotate($src, 180, 0);
            } elseif ($ori === 6) {
                $src = imagerotate($src, -90, 0);
                [$w, $h] = [$h, $w];
            } elseif ($ori === 8) {
                $src = imagerotate($src, 90, 0);
                [$w, $h] = [$h, $w];
            }
        }

        $nw = $w;
        $nh = $h;
        if ($w > $maxDim || $h > $maxDim) {
            if ($w >= $h) {
                $nw = $maxDim;
                $nh = max(1, (int) round($h * $maxDim / $w));
            } else {
                $nh = $maxDim;
                $nw = max(1, (int) round($w * $maxDim / $h));
            }
        }

        $dst = imagecreatetruecolor($nw, $nh);
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $trans);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($src);

        $tmp = $filePath . '.tmp';
        $ok  = match ($type) {
            IMAGETYPE_JPEG => @imagejpeg($dst, $tmp, $quality),
            IMAGETYPE_PNG  => @imagepng($dst, $tmp, 7),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? @imagewebp($dst, $tmp, $quality) : false,
            default        => false,
        };
        imagedestroy($dst);

        if ($ok && is_file($tmp)) {
            if (filesize($tmp) < filesize($filePath) || $w > $maxDim || $h > $maxDim) {
                @rename($tmp, $filePath);
            } else {
                @unlink($tmp);
            }
        }
    }
}
