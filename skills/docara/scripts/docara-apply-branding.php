#!/usr/bin/env php
<?php

declare(strict_types=1);

$options = getopt('', ['root::', 'title:', 'logo-svg::', 'keep-theme-builder', 'write', 'json', 'help']);

if (isset($options['help']) || ! isset($options['title'])) {
    echo "Usage: php docara-apply-branding.php --title='Project' [--root=.] [--logo-svg=/path/logo.svg] [--keep-theme-builder] [--write] [--json]\n";
    echo "Applies project branding to a Docara site and removes the SIMAI UI theme-builder demo by default.\n";
    exit(isset($options['title']) ? 0 : 1);
}

$root = realpath((string) ($options['root'] ?? getcwd()));
if ($root === false || ! is_dir($root)) {
    fwrite(STDERR, "Invalid root\n");
    exit(2);
}

$title = trim((string) $options['title']);
if ($title === '') {
    fwrite(STDERR, "Invalid --title\n");
    exit(2);
}

$logoSvg = null;
if (! empty($options['logo-svg'])) {
    $logoPath = (string) $options['logo-svg'];
    $logoPath = str_starts_with($logoPath, DIRECTORY_SEPARATOR) ? $logoPath : getcwd() . DIRECTORY_SEPARATOR . $logoPath;
    if (! is_file($logoPath)) {
        fwrite(STDERR, "Logo SVG not found: {$logoPath}\n");
        exit(2);
    }
    $logoSvg = trim((string) file_get_contents($logoPath));
}

$write = isset($options['write']);
$keepThemeBuilder = isset($options['keep-theme-builder']);
$actions = [];

apply_config_brand($root . '/config.php', $title, $logoSvg, $write, $actions);
apply_logo_component($root . '/source/_core/_components/header/logo.blade.php', $write, $actions);
apply_logo_css($root . '/source/_core/_assets/css/base.scss', $write, $actions);
if (! $keepThemeBuilder) {
    remove_theme_builder($root . '/source/_core/_layouts/main.blade.php', $write, $actions);
}

if (isset($options['json'])) {
    echo json_encode(['actions' => $actions], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    foreach ($actions as $action) {
        $line = strtoupper($action['status']) . "\t" . $action['path'];
        if (! empty($action['detail'])) {
            $line .= "\t" . $action['detail'];
        }
        echo $line . PHP_EOL;
    }
    if (! $write) {
        echo "Dry run only. Re-run with --write to update files.\n";
    }
}

function apply_config_brand(string $path, string $title, ?string $logoSvg, bool $write, array &$actions): void
{
    if (! is_file($path)) {
        $actions[] = ['status' => 'missing', 'path' => $path, 'detail' => 'config.php not found'];
        return;
    }

    $config = (string) file_get_contents($path);
    $brand = "    'brand' => [\n"
        . "        'title' => " . var_export($title, true) . ",\n"
        . "        'logoSvg' => " . ($logoSvg === null ? 'null' : var_export($logoSvg, true)) . ",\n"
        . "    ],";

    if (preg_match("/^[ \t]*['\"]brand['\"]\\s*=>\\s*\\[[\\s\\S]*?^[ \t]*\\],[ \t]*$/m", $config)) {
        $updated = preg_replace("/^[ \t]*['\"]brand['\"]\\s*=>\\s*\\[[\\s\\S]*?^[ \t]*\\],[ \t]*$/m", $brand, $config, 1);
    } elseif (preg_match("/^[ \t]*['\"]siteDescription['\"]\\s*=>.*$/m", $config, $match, PREG_OFFSET_CAPTURE)) {
        $lineEnd = strpos($config, "\n", $match[0][1]);
        $lineEnd = $lineEnd === false ? strlen($config) : $lineEnd + 1;
        $updated = substr($config, 0, $lineEnd) . $brand . "\n" . substr($config, $lineEnd);
    } else {
        $updated = preg_replace('/return\\s+\\[\\s*/', "return [\n" . $brand . "\n", $config, 1);
    }

    if (! is_string($updated) || $updated === $config) {
        $actions[] = ['status' => 'exists', 'path' => $path, 'detail' => 'brand unchanged'];
        return;
    }

    $actions[] = ['status' => $write ? 'updated' : 'would-update', 'path' => $path, 'detail' => "brand.title={$title}"];
    if ($write) {
        file_put_contents($path, $updated);
    }
}

function apply_logo_component(string $path, bool $write, array &$actions): void
{
    $content = <<<'BLADE'
@php
    $brand = $page->brand ?? [];
    $brandTitle = $brand['title'] ?? $page->siteName ?? 'Documentation';
    $brandLogoSvg = $brand['logoSvg'] ?? null;
    $brandHomeUrl = rtrim((string) ($page->baseUrl ?? '/'), '/') ?: '/';
@endphp

<a href="{{ $brandHomeUrl }}" title="{{ $page->siteName }} home" class="logo sf-logo inline-flex items-center">
    <span class="sf-logo-mark" aria-hidden="true">
        @if($brandLogoSvg)
            {!! $brandLogoSvg !!}
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32">
                <path fill="#e81123" d="M0 3.2C0 1.433 1.428 0 3.19 0h25.514a3.195 3.195 0 0 1 3.189 3.2v25.6c0 1.767-1.428 3.2-3.19 3.2H3.19A3.195 3.195 0 0 1 0 28.8z"/>
                <path fill="#f7f7f7" fill-rule="evenodd" d="m4.629 16 8.541-8.602 2.776 2.796-5.766 5.807 5.765 5.807-2.775 2.795z" clip-rule="evenodd"/>
                <path fill="#f7f7f7" fill-rule="evenodd" d="M27.262 16 18.72 7.399l-2.776 2.796 5.766 5.807-5.765 5.807 2.775 2.795z" clip-rule="evenodd"/>
            </svg>
        @endif
    </span>
    <span class="sf-logo-title">{{ $brandTitle }}</span>
</a>
BLADE;

    $current = is_file($path) ? (string) file_get_contents($path) : '';
    if ($current === $content . "\n") {
        $actions[] = ['status' => 'exists', 'path' => $path, 'detail' => 'project logo component already installed'];
        return;
    }

    $actions[] = ['status' => $write ? 'updated' : 'would-update', 'path' => $path, 'detail' => 'project logo component'];
    if ($write) {
        ensure_dir(dirname($path));
        file_put_contents($path, $content . "\n");
    }
}

function apply_logo_css(string $path, bool $write, array &$actions): void
{
    $block = <<<'SCSS'

/* Project header branding */
.header--wrap {
  padding-inline: 1rem !important;
}

.sf-logo {
  gap: var(--sf-b3);
  color: var(--sf-on-surface);
  text-decoration: none;
  min-width: 0;

  &-mark {
    display: inline-flex;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;

    svg {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  &-title {
    display: inline-block;
    max-width: 220px;
    overflow: hidden;
    color: var(--sf-on-surface);
    font-size: var(--sf-b6);
    line-height: var(--sf-c2);
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
SCSS;

    $current = is_file($path) ? (string) file_get_contents($path) : '';
    if (str_contains($current, 'Project header branding') || str_contains($current, '.sf-logo-title')) {
        $actions[] = ['status' => 'exists', 'path' => $path, 'detail' => 'logo CSS already present'];
        return;
    }

    $actions[] = ['status' => $write ? 'updated' : 'would-update', 'path' => $path, 'detail' => 'project logo CSS'];
    if ($write) {
        ensure_dir(dirname($path));
        $prefix = $current !== '' && ! str_ends_with($current, "\n") ? "\n" : '';
        file_put_contents($path, $current . $prefix . $block . "\n");
    }
}

function remove_theme_builder(string $path, bool $write, array &$actions): void
{
    if (! is_file($path)) {
        $actions[] = ['status' => 'missing', 'path' => $path, 'detail' => 'main layout not found'];
        return;
    }

    $current = (string) file_get_contents($path);
    $updated = preg_replace('/^.*(?:data-theme-builder|sf-theme-builder).*\\R?/m', '', $current);
    if (! is_string($updated) || $updated === $current) {
        $actions[] = ['status' => 'exists', 'path' => $path, 'detail' => 'theme builder absent'];
        return;
    }

    $actions[] = ['status' => $write ? 'updated' : 'would-update', 'path' => $path, 'detail' => 'removed theme builder demo'];
    if ($write) {
        file_put_contents($path, $updated);
    }
}

function ensure_dir(string $dir): void
{
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}
