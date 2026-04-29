#!/usr/bin/env php
<?php

declare(strict_types=1);

$options = getopt('', [
    'input:',
    'output:',
    'locale::',
    'title::',
    'include::',
    'exclude::',
    'write',
    'help',
]);

if (isset($options['help']) || empty($options['input']) || empty($options['output'])) {
    echo "Usage: php import-markdown-docs.php --input=<repo> --output=<docara-project> [--locale=en] [--title='Documentation'] [--include=path1,path2] [--exclude=node_modules,vendor,source,docara] [--write]\n";
    echo "Without --write the script prints planned imports only.\n";
    exit(isset($options['help']) ? 0 : 2);
}

$inputRoot = realpath((string) $options['input']);
if ($inputRoot === false || ! is_dir($inputRoot)) {
    fwrite(STDERR, "Invalid input root\n");
    exit(2);
}

$outputRoot = rtrim((string) $options['output'], "/\\");
$locale = strtolower((string) ($options['locale'] ?? 'en'));
$title = (string) ($options['title'] ?? 'Documentation');
$write = isset($options['write']);
$include = array_values(array_filter(array_map('trim', explode(',', (string) ($options['include'] ?? '')))));
$exclude = array_values(array_filter(array_map('trim', explode(',', (string) ($options['exclude'] ?? '.git,node_modules,vendor,source,docara,build_production')))));

function normalize_rel(string $path): string
{
    return trim(str_replace('\\', '/', $path), '/');
}

function should_include(string $relative, array $include, array $exclude): bool
{
    $relative = normalize_rel($relative);
    foreach ($exclude as $prefix) {
        $prefix = normalize_rel($prefix);
        if ($prefix !== '' && ($relative === $prefix || str_starts_with($relative, $prefix . '/'))) {
            return false;
        }
    }
    if (! $include) {
        return true;
    }
    foreach ($include as $prefix) {
        $prefix = normalize_rel($prefix);
        if ($relative === $prefix || str_starts_with($relative, $prefix . '/')) {
            return true;
        }
    }
    return false;
}

function human_title(string $slug): string
{
    $slug = preg_replace('/\.(md|mdx)$/i', '', basename($slug)) ?? basename($slug);
    $slug = preg_replace('/[-_]+/', ' ', $slug) ?? $slug;
    return mb_convert_case(trim($slug), MB_CASE_TITLE, 'UTF-8');
}

function front_matter(string $title): string
{
    return "---\nextends: _core._layouts.documentation\nsection: content\ntitle: {$title}\ndescription: {$title}\n---\n\n";
}

function strip_existing_front_matter(string $text): string
{
    return preg_replace('/^---\R.*?\R---\R*/s', '', $text, 1) ?? $text;
}

$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inputRoot, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (! $file->isFile() || ! preg_match('/\.mdx?$/i', $file->getFilename())) {
        continue;
    }
    $relative = normalize_rel(substr($file->getPathname(), strlen($inputRoot)));
    if (! should_include($relative, $include, $exclude)) {
        continue;
    }
    $files[] = $relative;
}
sort($files);

$docsRoot = $outputRoot . '/source/docs/' . $locale;
$planned = [];
$menusByDir = [];
$settingsDirs = ['' => true];

foreach ($files as $relative) {
    $sourcePath = $inputRoot . '/' . $relative;
    $dir = normalize_rel(dirname($relative));
    $base = basename($relative);
    $targetName = preg_match('/^README\.mdx?$/i', $base) ? 'index.md' : preg_replace('/\.mdx$/i', '.md', $base);
    $targetRel = ($dir === '.' || $dir === '') ? $targetName : $dir . '/' . $targetName;
    $targetPath = $docsRoot . '/' . $targetRel;
    $pageSlug = preg_replace('/\.md$/', '', $targetName) ?? $targetName;
    $targetDir = $dir === '.' ? '' : $dir;
    $settingsDirs[$targetDir] = true;
    if ($pageSlug !== 'index') {
        $menusByDir[$targetDir][$pageSlug] = human_title($base);
    }
    $parts = $targetDir === '' ? [] : explode('/', $targetDir);
    $parent = '';
    foreach ($parts as $part) {
        $child = $parent === '' ? $part : $parent . '/' . $part;
        $settingsDirs[$parent] = true;
        $settingsDirs[$child] = true;
        $menusByDir[$parent][$part] = human_title($part);
        $parent = $child;
    }
    $planned[] = ['source' => $relative, 'target' => $targetRel, 'title' => human_title($base), 'source_path' => $sourcePath, 'target_path' => $targetPath];
}

foreach ($planned as $item) {
    echo ($write ? 'IMPORT' : 'WOULD_IMPORT') . "\t" . $item['source'] . "\t" . $item['target'] . PHP_EOL;
    if (! $write) {
        continue;
    }
    $content = strip_existing_front_matter((string) file_get_contents($item['source_path']));
    $dir = dirname($item['target_path']);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($item['target_path'], front_matter($item['title']) . $content);
}

if ($write) {
    if (! is_dir($docsRoot)) {
        mkdir($docsRoot, 0777, true);
    }
    file_put_contents($docsRoot . '/.lang.php', "<?php\nreturn [\n    'search' => 'Search',\n    'edit article' => 'Edit article',\n];\n");
    foreach (array_keys($settingsDirs) as $dir) {
        $menu = $menusByDir[$dir] ?? [];
        ksort($menu);
        $settingsPath = $docsRoot . ($dir === '' ? '' : '/' . $dir) . '/.settings.php';
        if (! is_dir(dirname($settingsPath))) {
            mkdir(dirname($settingsPath), 0777, true);
        }
        $settings = [
            'title' => $dir === '' ? $title : human_title($dir),
            'showInMenu' => true,
            'order' => 10,
            'menu' => $menu,
        ];
        file_put_contents($settingsPath, "<?php\nreturn " . var_export($settings, true) . ";\n");
    }
}

if (! $write) {
    echo "Dry run only. Re-run with --write to import files.\n";
}
