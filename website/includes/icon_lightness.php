<?php

/**
 * Ürün ikon kütüphanesi: çoğunlukla açık tonlu (beyaza yakın) ve düşük doygunlukta
 * görselleri tespit edip renkleri tersler — açık gri kutuda kaybolan beyaz ikonlar için.
 *
 * - PNG / WebP: şeffaflık korunur; yalnızca opak piksellerde RGB negatiflenir.
 * - SVG: yaygın beyaz fill/stroke sabitleri koyu griye çevrilir (basit metin düzeyi).
 * - JPEG: opak beyaz arka plan riski nedeniyle dokunulmaz.
 */

declare(strict_types=1);

/**
 * @param  string $absPath Sunucudaki mutlak dosya yolu
 * @return bool   Dosya değiştirildiyse true
 */
function fx_catalog_icon_postprocess(string $absPath): bool
{
    if (!is_file($absPath) || !is_readable($absPath)) {
        return false;
    }

    $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

    if ($ext === 'svg') {
        return fx_catalog_icon_postprocess_svg($absPath);
    }

    if (!in_array($ext, ['png', 'webp'], true)) {
        return false;
    }

    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }

    $im = match ($ext) {
        'png' => @imagecreatefrompng($absPath),
        'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absPath) : false,
        default => false,
    };

    if (!$im) {
        return false;
    }

    if (!imageistruecolor($im)) {
        $w = imagesx($im);
        $h = imagesy($im);
        $tc = imagecreatetruecolor($w, $h);
        if (!$tc) {
            imagedestroy($im);
            return false;
        }
        imagealphablending($tc, false);
        imagesavealpha($tc, true);
        $transparent = imagecolorallocatealpha($tc, 0, 0, 0, 127);
        imagefilledrectangle($tc, 0, 0, $w - 1, $h - 1, $transparent);
        imagealphablending($tc, true);
        imagecopy($tc, $im, 0, 0, 0, 0, $w, $h);
        imagealphablending($tc, false);
        imagesavealpha($tc, true);
        imagedestroy($im);
        $im = $tc;
    }

    imagealphablending($im, false);
    imagesavealpha($im, true);

    $w = imagesx($im);
    $h = imagesy($im);
    if ($w < 1 || $h < 1) {
        imagedestroy($im);
        return false;
    }

    $stepX = max(1, (int) ($w / 56));
    $stepY = max(1, (int) ($h / 56));

    $opaque       = 0;
    $lightLowSat  = 0;

    for ($y = 0; $y < $h; $y += $stepY) {
        for ($x = 0; $x < $w; $x += $stepX) {
            $sample = fx_icon_sample_pixel($im, $x, $y);
            if ($sample === null) {
                continue;
            }
            [$r, $g, $b, $a] = $sample;
            if ($a >= 96) {
                continue;
            }
            $opaque++;
            $l   = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255.0;
            $sat = max($r, $g, $b) - min($r, $g, $b);
            if ($l >= 0.80 && $sat < 48) {
                $lightLowSat++;
            }
        }
    }

    if ($opaque < 8 || ($lightLowSat / $opaque) < 0.75) {
        imagedestroy($im);
        return false;
    }

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $sample = fx_icon_sample_pixel($im, $x, $y);
            if ($sample === null) {
                continue;
            }
            [$r, $g, $b, $a] = $sample;
            if ($a >= 96) {
                continue;
            }
            $nr = 255 - $r;
            $ng = 255 - $g;
            $nb = 255 - $b;
            $col = imagecolorallocatealpha($im, $nr, $ng, $nb, $a);
            if ($col !== false) {
                imagesetpixel($im, $x, $y, $col);
            }
        }
    }

    $ok = match ($ext) {
        'png' => imagepng($im, $absPath, 6),
        'webp' => function_exists('imagewebp') ? imagewebp($im, $absPath, 92) : false,
        default => false,
    };

    imagedestroy($im);

    return (bool) $ok;
}

/**
 * @return array{0:int,1:int,2:int,3:int}|null [r,g,b,alpha] alpha 0=opak, 127=şeffaf (GD)
 */
function fx_icon_sample_pixel($im, int $x, int $y): ?array
{
    if ($x < 0 || $y < 0 || $x >= imagesx($im) || $y >= imagesy($im)) {
        return null;
    }
    $rgba = imagecolorat($im, $x, $y);
    $a    = ($rgba >> 24) & 0x7F;
    $r    = ($rgba >> 16) & 0xFF;
    $g    = ($rgba >> 8) & 0xFF;
    $b    = $rgba & 0xFF;

    return [$r, $g, $b, $a];
}

function fx_catalog_icon_postprocess_svg(string $absPath): bool
{
    $s = @file_get_contents($absPath);
    if ($s === false || $s === '') {
        return false;
    }

    $orig = $s;

    $pairs = [
        ['~fill\s*=\s*"#fff"~i', 'fill="#1f2933"'],
        ["~fill\s*=\s*'#fff'~i", "fill='#1f2933'"],
        ['~fill\s*=\s*"#ffffff"~i', 'fill="#1f2933"'],
        ["~fill\s*=\s*'#ffffff'~i", "fill='#1f2933'"],
        ['~fill\s*=\s*"#FFFFFF"~i', 'fill="#1f2933"'],
        ['~fill\s*=\s*"white"~i', 'fill="#1f2933"'],
        ["~fill\s*=\s*'white'~i", "fill='#1f2933'"],
        ['~fill\s*=\s*"rgb\(\s*255\s*,\s*255\s*,\s*255\s*\)"~i', 'fill="#1f2933"'],
        ['~stroke\s*=\s*"#fff"~i', 'stroke="#1f2933"'],
        ["~stroke\s*=\s*'#fff'~i", "stroke='#1f2933'"],
        ['~stroke\s*=\s*"#ffffff"~i', 'stroke="#1f2933"'],
        ["~stroke\s*=\s*'#ffffff'~i", "stroke='#1f2933'"],
        ['~stroke\s*=\s*"white"~i', 'stroke="#1f2933"'],
        ["~stroke\s*=\s*'white'~i", "stroke='#1f2933'"],
    ];

    foreach ($pairs as [$pat, $repl]) {
        $s = preg_replace($pat, $repl, $s) ?? $s;
    }

    if ($s === $orig) {
        return false;
    }

    return (bool) file_put_contents($absPath, $s);
}
