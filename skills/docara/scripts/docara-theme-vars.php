#!/usr/bin/env php
<?php

declare(strict_types=1);

$options = getopt('', [
    'seed:',
    'root::',
    'output::',
    'install',
    'write',
    'include-status',
    'json',
    'help',
]);

if (isset($options['help']) || ! isset($options['seed'])) {
    echo "Usage: php docara-theme-vars.php --seed=#E81123 [--root=.] [--output=source/_core/_assets/css/_theme.generated.scss] [--write] [--install] [--include-status] [--json]\n";
    echo "Generates SIMAI UI CSS variable overrides for a Docara theme from one seed color.\n";
    exit(isset($options['seed']) ? 0 : 1);
}

$root = realpath((string) ($options['root'] ?? getcwd()));
if ($root === false || ! is_dir($root)) {
    fwrite(STDERR, "Invalid root\n");
    exit(2);
}

$seed = normalize_hex((string) $options['seed']);
if ($seed === null) {
    fwrite(STDERR, "Invalid --seed. Use #RRGGBB or RRGGBB.\n");
    exit(2);
}

$output = (string) ($options['output'] ?? 'source/_core/_assets/css/_theme.generated.scss');
$outputPath = str_starts_with($output, DIRECTORY_SEPARATOR) ? $output : $root . DIRECTORY_SEPARATOR . $output;
$write = isset($options['write']) || isset($options['install']);
$install = isset($options['install']);
$includeStatus = isset($options['include-status']);

$palettes = build_palettes($seed, $includeStatus);
$css = render_css($seed, $palettes, $includeStatus);
$actions = [];

if ($write) {
    $dir = dirname($outputPath);
    if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
        fwrite(STDERR, "Cannot create output directory: {$dir}\n");
        exit(2);
    }
    file_put_contents($outputPath, $css);
    $actions[] = ['status' => 'written', 'path' => $outputPath];
} else {
    echo $css;
}

if ($install) {
    $mainScss = $root . '/source/_core/_assets/css/main.scss';
    if (! is_file($mainScss)) {
        fwrite(STDERR, "Cannot install import: {$mainScss} not found\n");
        exit(2);
    }
    $importName = import_name_for_scss($mainScss, $outputPath);
    $importLine = '@import "' . $importName . '";';
    $main = (string) file_get_contents($mainScss);
    if (! preg_match('/@import\s+["\']' . preg_quote($importName, '/') . '["\']\s*;/', $main)) {
        $prefix = $main !== '' && ! str_ends_with($main, "\n") ? "\n" : '';
        file_put_contents($mainScss, $main . $prefix . $importLine . "\n");
        $actions[] = ['status' => 'updated', 'path' => $mainScss, 'detail' => $importLine];
    } else {
        $actions[] = ['status' => 'exists', 'path' => $mainScss, 'detail' => $importLine];
    }
}

if (isset($options['json'])) {
    echo json_encode([
        'seed' => $seed,
        'output' => $write ? $outputPath : null,
        'installed' => $install,
        'includeStatus' => $includeStatus,
        'palettes' => $palettes,
        'actions' => $actions,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} elseif ($write) {
    foreach ($actions as $action) {
        $line = strtoupper($action['status']) . "\t" . $action['path'];
        if (! empty($action['detail'])) {
            $line .= "\t" . $action['detail'];
        }
        echo $line . PHP_EOL;
    }
}

function normalize_hex(string $hex): ?string
{
    $hex = trim($hex);
    $hex = ltrim($hex, '#');
    if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return null;
    }
    return '#' . strtoupper($hex);
}

function build_palettes(string $seed, bool $includeStatus): array
{
    $primary = hex_to_hsl($seed);
    $secondary = shift_hsl($primary, 8.0, 0.48, 0.03);
    $tertiary = shift_hsl($primary, 58.0, 0.62, 0.05);
    $neutral = shift_hsl($primary, 8.0, 0.10, 0.02);

    $palettes = [
        'primary' => palette_from_hsl($primary, $seed),
        'secondary' => palette_from_hsl($secondary),
        'tertiary' => palette_from_hsl($tertiary),
        'neutral' => palette_from_hsl($neutral),
        'info' => palette_from_hsl($primary, $seed),
    ];

    if ($includeStatus) {
        $palettes['success'] = palette_from_hsl(shift_hsl($primary, 130.0, 0.78, 0.00));
        $palettes['warning'] = palette_from_hsl(shift_hsl($primary, 42.0, 0.90, 0.04));
        $palettes['error'] = palette_from_hsl($primary, $seed);
    }

    return $palettes;
}

function palette_from_hsl(array $hsl, ?string $anchor50 = null): array
{
    $tones = [
        5 => 0.08,
        10 => 0.13,
        15 => 0.18,
        20 => 0.23,
        25 => 0.28,
        30 => 0.33,
        35 => 0.38,
        40 => 0.43,
        50 => 0.52,
        60 => 0.63,
        70 => 0.73,
        80 => 0.82,
        85 => 0.87,
        90 => 0.91,
        95 => 0.96,
        98 => 0.985,
    ];

    $palette = [];
    foreach ($tones as $tone => $lightness) {
        $satBoost = $tone < 50 ? 1.08 : 0.98;
        $palette[(string) $tone] = $tone === 50 && $anchor50
            ? $anchor50
            : hsl_to_hex([
            'h' => $hsl['h'],
            's' => clamp($hsl['s'] * $satBoost, 0.0, 1.0),
            'l' => clamp($lightness, 0.0, 1.0),
        ]);
    }
    return $palette;
}

function shift_hsl(array $hsl, float $hueShift, float $saturationRatio, float $lightnessShift): array
{
    return [
        'h' => fmod($hsl['h'] + $hueShift + 360.0, 360.0),
        's' => clamp($hsl['s'] * $saturationRatio, 0.04, 0.92),
        'l' => clamp($hsl['l'] + $lightnessShift, 0.12, 0.78),
    ];
}

function render_css(string $seed, array $palettes, bool $includeStatus): string
{
    $alphaTones = [10, 40, 50, 90];
    $alphas = [4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44, 48];
    $lines = [
        '/**',
        ' * Generated by docara-theme-vars.php.',
        ' * Seed: ' . $seed,
        ' * Import this file after SIMAI UI core.css.',
        ' */',
        ':root {',
    ];

    foreach ($palettes as $name => $palette) {
        $lines[] = "  /* {$name} */";
        foreach ($palette as $tone => $hex) {
            $lines[] = "  --sf-{$name}-{$tone}: {$hex};";
        }
        foreach ($alphaTones as $tone) {
            if (! isset($palette[(string) $tone])) {
                continue;
            }
            $rgb = hex_to_rgb($palette[(string) $tone]);
            foreach ($alphas as $alpha) {
                $opacity = rtrim(rtrim(sprintf('%.2F', $alpha / 100), '0'), '.');
                $lines[] = "  --sf-{$name}-{$tone}--alfa-{$alpha}: rgba({$rgb['r']}, {$rgb['g']}, {$rgb['b']}, {$opacity});";
            }
        }
    }

    $lines[] = '}';
    if (! $includeStatus) {
        $lines[] = '';
        $lines[] = '/* Status colors are intentionally left on SIMAI UI defaults. Use --include-status only for fully branded status semantics. */';
    }

    return implode("\n", $lines) . "\n";
}

function import_name_for_scss(string $mainScss, string $outputPath): string
{
    $relative = ltrim(str_replace(dirname($mainScss), '', $outputPath), DIRECTORY_SEPARATOR);
    $relative = preg_replace('/\.s[ac]ss$/', '', $relative) ?? $relative;
    $relative = preg_replace('/(^|\/)_([^\/]+)$/', '$1$2', $relative) ?? $relative;
    return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
}

function hex_to_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2)),
    ];
}

function rgb_to_hex(int $r, int $g, int $b): string
{
    return sprintf('#%02X%02X%02X', clamp_int($r), clamp_int($g), clamp_int($b));
}

function hex_to_hsl(string $hex): array
{
    $rgb = hex_to_rgb($hex);
    $r = $rgb['r'] / 255;
    $g = $rgb['g'] / 255;
    $b = $rgb['b'] / 255;
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $l = ($max + $min) / 2;
    $h = 0.0;
    $s = 0.0;

    if ($max !== $min) {
        $delta = $max - $min;
        $s = $l > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);
        if ($max === $r) {
            $h = (($g - $b) / $delta + ($g < $b ? 6 : 0)) * 60;
        } elseif ($max === $g) {
            $h = (($b - $r) / $delta + 2) * 60;
        } else {
            $h = (($r - $g) / $delta + 4) * 60;
        }
    }

    return ['h' => $h, 's' => $s, 'l' => $l];
}

function hsl_to_hex(array $hsl): string
{
    $h = fmod((float) $hsl['h'] + 360.0, 360.0) / 360;
    $s = clamp((float) $hsl['s'], 0.0, 1.0);
    $l = clamp((float) $hsl['l'], 0.0, 1.0);

    if ($s === 0.0) {
        $v = (int) round($l * 255);
        return rgb_to_hex($v, $v, $v);
    }

    $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
    $p = 2 * $l - $q;
    $r = hue_to_rgb($p, $q, $h + 1 / 3);
    $g = hue_to_rgb($p, $q, $h);
    $b = hue_to_rgb($p, $q, $h - 1 / 3);

    return rgb_to_hex((int) round($r * 255), (int) round($g * 255), (int) round($b * 255));
}

function hue_to_rgb(float $p, float $q, float $t): float
{
    if ($t < 0) {
        $t += 1;
    }
    if ($t > 1) {
        $t -= 1;
    }
    if ($t < 1 / 6) {
        return $p + ($q - $p) * 6 * $t;
    }
    if ($t < 1 / 2) {
        return $q;
    }
    if ($t < 2 / 3) {
        return $p + ($q - $p) * (2 / 3 - $t) * 6;
    }
    return $p;
}

function clamp(float $value, float $min, float $max): float
{
    return max($min, min($max, $value));
}

function clamp_int(int $value): int
{
    return max(0, min(255, $value));
}
