<?php

namespace App\Libraries;

/**
 * Pure-PHP QR Code generator (ISO/IEC 18004, Byte mode, ECC Level M, Versions 1-10)
 * using PHP GD to output a crisp base64 PNG data URI for Dompdf & browser print.
 */
class QrCodeGenerator
{
    private const GF_EXP = [
        1,2,4,8,16,32,64,128,29,58,116,232,205,135,19,38,76,152,45,90,180,117,234,201,143,3,6,12,24,48,96,192,
        157,39,78,156,37,74,148,53,106,212,181,119,238,193,159,35,70,140,5,10,20,40,80,160,93,186,105,210,185,111,222,161,
        95,190,97,194,153,47,94,188,101,202,137,15,30,60,120,240,253,231,211,187,107,214,177,127,254,225,223,163,91,182,113,226,
        217,175,67,134,17,34,68,136,13,26,52,104,208,189,103,206,129,31,62,124,248,237,199,147,59,118,236,197,151,51,102,204,
        133,23,46,92,184,109,218,169,79,158,33,66,132,21,42,84,168,77,154,41,82,164,85,170,73,146,57,114,228,213,183,115,
        230,209,191,99,198,145,63,126,252,229,215,179,123,246,241,255,227,219,171,75,150,49,98,196,149,55,110,220,165,87,174,65,
        130,25,50,100,200,141,7,14,28,56,112,224,221,167,83,166,81,162,89,178,121,242,249,239,195,155,43,86,172,69,138,9,
        18,36,72,144,61,122,244,245,247,243,251,235,203,139,11,22,44,88,176,125,250,233,207,131,27,54,108,216,173,71,142,1,
    ];

    private const GF_LOG = [
        0,0,1,25,2,50,26,198,3,223,51,238,27,104,199,75,4,100,224,14,52,141,239,129,28,193,105,248,200,8,76,113,
        5,138,101,47,225,36,15,33,53,147,142,218,240,18,130,69,29,181,194,125,106,39,249,185,201,154,9,120,77,228,114,166,
        6,191,139,98,102,221,48,253,226,152,37,179,16,145,34,136,54,208,148,206,143,150,219,189,241,210,19,92,131,56,70,64,
        30,66,182,163,195,72,126,110,107,58,40,84,250,133,186,61,202,94,155,159,10,21,121,43,78,212,229,172,115,243,167,87,
        7,112,192,247,140,128,99,13,103,74,222,237,49,197,254,24,227,165,153,119,38,184,180,124,17,68,146,217,35,32,137,46,
        55,63,209,91,149,188,207,205,144,135,151,178,220,252,190,97,242,86,211,171,20,42,93,158,132,60,57,83,71,109,65,162,
        31,45,67,216,183,123,164,118,196,23,73,236,127,12,111,246,108,161,59,82,41,157,85,170,251,96,134,177,187,204,62,90,
        203,89,95,176,156,169,160,81,11,245,22,235,122,117,44,215,79,174,213,233,230,231,173,232,116,214,244,234,168,80,88,175,
    ];

    /**
     * Level M parameters for Versions 1..10:
     * [totalDataBytes, ecBytesPerBlock, g1Blocks, g1Data, g2Blocks, g2Data, alignCenters[]]
     */
    private const VERSIONS_M = [
        1  => [16,  10, 1, 16, 0, 0, []],
        2  => [28,  16, 1, 28, 0, 0, [6, 18]],
        3  => [44,  26, 1, 44, 0, 0, [6, 22]],
        4  => [64,  18, 2, 32, 0, 0, [6, 26]],
        5  => [86,  24, 2, 43, 0, 0, [6, 30]],
        6  => [108, 16, 4, 27, 0, 0, [6, 34]],
        7  => [124, 18, 4, 31, 0, 0, [6, 22, 38]],
        8  => [154, 22, 2, 38, 2, 39, [6, 24, 42]],
        9  => [182, 22, 3, 36, 2, 37, [6, 26, 46]],
        10 => [216, 26, 4, 43, 1, 44, [6, 28, 50]],
    ];

    public static function buildUpiUri(string $upiId, string $payeeName, string $description): string
    {
        $params = [
            'pa' => trim($upiId),
            'pn' => mb_substr(trim($payeeName), 0, 50),
            'cu' => 'INR',
            'tn' => mb_substr(trim(preg_replace('/\s+/', ' ', $description)), 0, 80),
        ];

        return 'upi://pay?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Render text into a base64 PNG data URI.
     */
    public static function toDataUri(string $text, int $modulePx = 3, int $quiet = 2): string
    {
        $matrix = self::encode($text);
        $n      = count($matrix);
        $size   = ($n + 2 * $quiet) * $modulePx;

        $im    = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $size - 1, $size - 1, $white);

        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($matrix[$y][$x]) {
                    $px = ($x + $quiet) * $modulePx;
                    $py = ($y + $quiet) * $modulePx;
                    imagefilledrectangle($im, $px, $py, $px + $modulePx - 1, $py + $modulePx - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    public static function encode(string $text): array
    {
        $bytes = array_values(unpack('C*', $text));
        $len   = count($bytes);

        $ver = null;
        foreach (self::VERSIONS_M as $v => $cfg) {
            $ccBits = $v >= 10 ? 16 : 8;
            if ((4 + $ccBits + 8 * $len) <= $cfg[0] * 8) {
                $ver = $v;
                break;
            }
        }
        if ($ver === null) {
            $bytes = array_slice($bytes, 0, 210);
            $len   = count($bytes);
            $ver   = 10;
        }

        [$totalData, $ecLen, $g1B, $g1D, $g2B, $g2D, $aligns] = self::VERSIONS_M[$ver];

        // Bitstream: Mode 0100 (Byte), char count, payload, terminator, pad
        $bits = '0100' . str_pad(decbin($len), $ver >= 10 ? 16 : 8, '0', STR_PAD_LEFT);
        foreach ($bytes as $b) {
            $bits .= str_pad(decbin($b), 8, '0', STR_PAD_LEFT);
        }
        $capBits = $totalData * 8;
        $bits   .= str_repeat('0', min(4, $capBits - strlen($bits)));
        while ((strlen($bits) % 8) !== 0) {
            $bits .= '0';
        }
        $padIdx = 0;
        while (strlen($bits) < $capBits) {
            $bits .= ($padIdx % 2 === 0) ? '11101100' : '00010001';
            $padIdx++;
        }

        $dataBytes = [];
        for ($i = 0; $i < $capBits; $i += 8) {
            $dataBytes[] = bindec(substr($bits, $i, 8));
        }

        // Split blocks & compute RS ECC
        $dataBlocks = [];
        $ecBlocks   = [];
        $offset     = 0;
        for ($i = 0; $i < $g1B + $g2B; $i++) {
            $dLen = $i < $g1B ? $g1D : $g2D;
            $blk  = array_slice($dataBytes, $offset, $dLen);
            $offset += $dLen;
            $dataBlocks[] = $blk;
            $ecBlocks[]   = self::rsEncode($blk, $ecLen);
        }

        // Interleave data then ECC
        $codewords = [];
        $maxD = max($g1D, $g2D);
        for ($i = 0; $i < $maxD; $i++) {
            foreach ($dataBlocks as $blk) {
                if (isset($blk[$i])) $codewords[] = $blk[$i];
            }
        }
        for ($i = 0; $i < $ecLen; $i++) {
            foreach ($ecBlocks as $blk) {
                $codewords[] = $blk[$i];
            }
        }

        $size   = 17 + 4 * $ver;
        $matrix = array_fill(0, $size, array_fill(0, $size, null));
        $isFunc = array_fill(0, $size, array_fill(0, $size, false));

        // Finders + separators
        self::placeFinder($matrix, $isFunc, 0, 0, $size);
        self::placeFinder($matrix, $isFunc, $size - 7, 0, $size);
        self::placeFinder($matrix, $isFunc, 0, $size - 7, $size);

        // Alignment patterns
        foreach ($aligns as $ay) {
            foreach ($aligns as $ax) {
                if ($isFunc[$ay][$ax]) continue;
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        $val = (max(abs($dx), abs($dy)) !== 1) ? 1 : 0;
                        $matrix[$ay + $dy][$ax + $dx] = $val;
                        $isFunc[$ay + $dy][$ax + $dx] = true;
                    }
                }
            }
        }

        // Timing + dark module
        for ($i = 8; $i < $size - 8; $i++) {
            $v = ($i % 2 === 0) ? 1 : 0;
            if (!$isFunc[6][$i]) { $matrix[6][$i] = $v; $isFunc[6][$i] = true; }
            if (!$isFunc[$i][6]) { $matrix[$i][6] = $v; $isFunc[$i][6] = true; }
        }
        $matrix[$size - 8][8] = 1;
        $isFunc[$size - 8][8] = true;

        // Reserve format & version info
        for ($i = 0; $i < 9; $i++) {
            if ($i !== 6) {
                $isFunc[8][$i] = true;
                $isFunc[$i][8] = true;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            $isFunc[8][$size - 1 - $i] = true;
            $isFunc[$size - 1 - $i][8] = true;
        }
        if ($ver >= 7) {
            $vBits = self::versionInfoBits($ver);
            for ($i = 0; $i < 18; $i++) {
                $bit = ($vBits >> $i) & 1;
                $r = intdiv($i, 3);
                $c = $size - 11 + ($i % 3);
                $matrix[$r][$c] = $bit; $isFunc[$r][$c] = true;
                $matrix[$c][$r] = $bit; $isFunc[$c][$r] = true;
            }
        }

        // Place data bits (zigzag)
        $bitIdx = 0;
        $totalBits = count($codewords) * 8;
        $dirUp = true;
        for ($col = $size - 1; $col > 0; $col -= 2) {
            if ($col === 6) $col--;
            for ($step = 0; $step < $size; $step++) {
                $y = $dirUp ? ($size - 1 - $step) : $step;
                for ($dx = 0; $dx < 2; $dx++) {
                    $x = $col - $dx;
                    if ($isFunc[$y][$x]) continue;
                    $bit = 0;
                    if ($bitIdx < $totalBits) {
                        $bit = ($codewords[intdiv($bitIdx, 8)] >> (7 - ($bitIdx % 8))) & 1;
                        $bitIdx++;
                    }
                    $matrix[$y][$x] = $bit;
                }
            }
            $dirUp = !$dirUp;
        }

        // Pick best mask 0..7
        $bestMask = 0;
        $bestScore = PHP_INT_MAX;
        $bestMat = $matrix;
        for ($m = 0; $m < 8; $m++) {
            $cand = $matrix;
            for ($y = 0; $y < $size; $y++) {
                for ($x = 0; $x < $size; $x++) {
                    if (!$isFunc[$y][$x] && self::maskCond($m, $y, $x)) {
                        $cand[$y][$x] ^= 1;
                    }
                }
            }
            self::applyFormatInfo($cand, $m, $size);
            $score = self::penalty($cand, $size);
            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMask = $m;
                $bestMat = $cand;
            }
        }

        return $bestMat;
    }

    private static function placeFinder(array &$m, array &$f, int $x0, int $y0, int $size): void
    {
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $x = $x0 + $dx; $y = $y0 + $dy;
                if ($x < 0 || $y < 0 || $x >= $size || $y >= $size) continue;
                $in = ($dx >= 0 && $dx <= 6 && $dy >= 0 && $dy <= 6);
                $on = $in && ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));
                $m[$y][$x] = $on ? 1 : 0;
                $f[$y][$x] = true;
            }
        }
    }

    private static function maskCond(int $m, int $r, int $c): bool
    {
        return match ($m) {
            0 => (($r + $c) % 2) === 0,
            1 => ($r % 2) === 0,
            2 => ($c % 3) === 0,
            3 => (($r + $c) % 3) === 0,
            4 => ((intdiv($r, 2) + intdiv($c, 3)) % 2) === 0,
            5 => (($r * $c) % 2 + ($r * $c) % 3) === 0,
            6 => ((($r * $c) % 2 + ($r * $c) % 3) % 2) === 0,
            default => ((($r + $c) % 2 + ($r * $c) % 3) % 2) === 0,
        };
    }

    private static function applyFormatInfo(array &$m, int $mask, int $size): void
    {
        // ECC Level M = 00
        $data = (0b00 << 3) | $mask;
        $rem  = $data << 10;
        for ($i = 14; $i >= 10; $i--) {
            if (($rem >> $i) & 1) $rem ^= (0b10100110111 << ($i - 10));
        }
        $fmt = (($data << 10) | $rem) ^ 0b101010000010010;

        for ($i = 0; $i < 15; $i++) {
            $bit = ($fmt >> $i) & 1;
            if ($i < 6) $m[$i][8] = $bit;
            elseif ($i < 8) $m[$i + 1][8] = $bit;
            else $m[$size - 15 + $i][8] = $bit;

            if ($i < 8) $m[8][$size - 1 - $i] = $bit;
            elseif ($i < 9) $m[8][7] = $bit;
            else $m[8][14 - $i] = $bit;
        }
    }

    private static function versionInfoBits(int $ver): int
    {
        $rem = $ver << 12;
        for ($i = 17; $i >= 12; $i--) {
            if (($rem >> $i) & 1) $rem ^= (0b1111100100101 << ($i - 12));
        }
        return ($ver << 12) | $rem;
    }

    private static function rsEncode(array $data, int $ecLen): array
    {
        $gen = [1];
        for ($i = 0; $i < $ecLen; $i++) {
            $next = array_fill(0, count($gen) + 1, 0);
            $factor = self::GF_EXP[$i];
            foreach ($gen as $j => $c) {
                $next[$j]     ^= $c;
                $next[$j + 1] ^= self::gfMul($c, $factor);
            }
            $gen = $next;
        }

        $res = array_merge($data, array_fill(0, $ecLen, 0));
        for ($i = 0; $i < count($data); $i++) {
            $coef = $res[$i];
            if ($coef !== 0) {
                for ($j = 1; $j < count($gen); $j++) {
                    $res[$i + $j] ^= self::gfMul($gen[$j], $coef);
                }
            }
        }
        return array_slice($res, count($data));
    }

    private static function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) return 0;
        return self::GF_EXP[(self::GF_LOG[$a] + self::GF_LOG[$b]) % 255];
    }

    private static function penalty(array $m, int $n): int
    {
        $score = 0;
        for ($y = 0; $y < $n; $y++) {
            $run = 1;
            for ($x = 1; $x < $n; $x++) {
                if ($m[$y][$x] === $m[$y][$x - 1]) { $run++; }
                else { if ($run >= 5) $score += $run - 2; $run = 1; }
            }
            if ($run >= 5) $score += $run - 2;
        }
        for ($x = 0; $x < $n; $x++) {
            $run = 1;
            for ($y = 1; $y < $n; $y++) {
                if ($m[$y][$x] === $m[$y - 1][$x]) { $run++; }
                else { if ($run >= 5) $score += $run - 2; $run = 1; }
            }
            if ($run >= 5) $score += $run - 2;
        }
        for ($y = 0; $y < $n - 1; $y++) {
            for ($x = 0; $x < $n - 1; $x++) {
                $s = $m[$y][$x] + $m[$y][$x + 1] + $m[$y + 1][$x] + $m[$y + 1][$x + 1];
                if ($s === 0 || $s === 4) $score += 3;
            }
        }
        return $score;
    }
}
