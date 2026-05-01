#!/usr/bin/env php
<?php

declare(strict_types=1);

$options = getopt('', ['root::', 'json', 'help']);

if (isset($options['help'])) {
    echo "Usage: php docara-doctor.php [--root=.] [--json]\n";
    exit(0);
}

$root = realpath((string) ($options['root'] ?? getcwd()));
if ($root === false || ! is_dir($root)) {
    fwrite(STDERR, "Invalid root\n");
    exit(2);
}

function read_json_file(string $path): ?array
{
    if (! is_file($path)) {
        return null;
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : null;
}

function parse_env_file(string $path): array
{
    if (! is_file($path)) {
        return [];
    }
    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        $value = trim($value, "\"'");
        $env[trim($key)] = $value;
    }
    return $env;
}

function find_locale_dirs(string $docsPath): array
{
    if (! is_dir($docsPath)) {
        return [];
    }
    $dirs = [];
    foreach (scandir($docsPath) ?: [] as $item) {
        if ($item === '.' || $item === '..' || str_starts_with($item, '.')) {
            continue;
        }
        if (is_dir($docsPath . DIRECTORY_SEPARATOR . $item)) {
            $dirs[] = $item;
        }
    }
    sort($dirs);
    return $dirs;
}

function config_locales_from_text(string $path): array
{
    if (! is_file($path)) {
        return [];
    }
    $text = (string) file_get_contents($path);
    if (! preg_match("/['\"]locales['\"]\\s*=>\\s*\\[(.*?)\\]/s", $text, $match)) {
        return [];
    }
    preg_match_all("/['\"]([a-z]{2}(?:-[a-z0-9]+)?)['\"]\\s*=>/i", $match[1], $items);
    $locales = array_map('strtolower', $items[1] ?? []);
    return array_values(array_unique($locales));
}

function package_version_from_lock(string $path, string $packageName): ?string
{
    $lock = read_json_file($path);
    if (! $lock) {
        return null;
    }
    foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $package) {
        if (($package['name'] ?? null) === $packageName) {
            return is_string($package['version'] ?? null) ? $package['version'] : null;
        }
    }
    return null;
}

function git_tracked_count(string $root, string $path): ?int
{
    if (! is_dir($root . '/.git') && ! is_dir(dirname($root) . '/.git')) {
        return null;
    }
    $relative = trim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
    if ($relative === '') {
        return null;
    }
    $command = 'git -C ' . escapeshellarg($root) . ' ls-files -- ' . escapeshellarg($relative);
    exec($command, $output, $code);
    if ($code !== 0) {
        return null;
    }
    return count(array_filter($output, static fn ($line) => trim((string) $line) !== ''));
}

$env = parse_env_file($root . '/.env');
$docsDir = trim($env['DOCS_DIR'] ?? getenv('DOCS_DIR') ?: 'docs', "/\\");
if ($docsDir === '') {
    $docsDir = 'docs';
}

$composer = read_json_file($root . '/composer.json');
$package = read_json_file($root . '/package.json');
$workflowRoots = [$root . '/.github/workflows'];
if (basename($root) === 'docara') {
    $workflowRoots[] = dirname($root) . '/.github/workflows';
}
$workflows = [];
foreach ($workflowRoots as $workflowRoot) {
    foreach (glob($workflowRoot . '/*.{yml,yaml}', GLOB_BRACE) ?: [] as $workflow) {
        $workflows[] = $workflow;
    }
}
$buildProduction = $root . '/build_production';
$docsPath = $root . '/source/' . $docsDir;
$localeDirs = find_locale_dirs($docsPath);
$configLocales = config_locales_from_text($root . '/config.php');
$docaraLockedVersion = package_version_from_lock($root . '/composer.lock', 'simai/docara');
$trackedCoreCount = git_tracked_count($root, $root . '/source/_core');

$checks = [];
$add = static function (string $name, string $status, string $detail = '') use (&$checks): void {
    $checks[] = ['name' => $name, 'status' => $status, 'detail' => $detail];
};

$requires = $composer['require'] ?? [];
$isDocaraPackageSource = ($composer['name'] ?? null) === 'simai/docara';
$add('composer.json', $composer ? 'ok' : 'missing', $composer ? 'found' : 'not found');
$add(
    'simai/docara package',
    isset($requires['simai/docara']) || $isDocaraPackageSource ? 'ok' : 'missing',
    $isDocaraPackageSource ? 'package source repository' : ($requires['simai/docara'] ?? 'not required')
);
$add('simai/docara locked', $docaraLockedVersion ? 'ok' : 'warn', $docaraLockedVersion ?? 'composer.lock not found or package not locked');
$add('vendor/bin/docara', is_file($root . '/vendor/bin/docara') || is_file($root . '/docara') ? 'ok' : 'missing', 'CLI entrypoint');
$add('.env', is_file($root . '/.env') ? 'ok' : 'missing', 'DOCS_DIR=' . $docsDir);
$add('config.php', is_file($root . '/config.php') ? 'ok' : 'missing', 'main Docara config');
$add('translate.config.php', is_file($root . '/translate.config.php') ? 'ok' : 'missing', 'translation config');
$add('source/_core', is_dir($root . '/source/_core') ? 'ok' : 'missing', 'Docara core files');
$add(
    'source/_core update',
    $trackedCoreCount ? 'warn' : 'ok',
    $trackedCoreCount
        ? "{$trackedCoreCount} tracked files; Docara init --update preserves them, compare vendor stubs and merge UI fixes manually"
        : 'not tracked or no git-tracked files detected'
);
$add('docs source', is_dir($docsPath) ? 'ok' : 'missing', 'source/' . $docsDir);
$add('locale folders', $localeDirs ? 'ok' : 'missing', implode(', ', $localeDirs) ?: 'none');
$add('config locales', $configLocales ? 'ok' : 'warn', implode(', ', $configLocales) ?: 'not detected by text scan');
$add('locale alignment', array_diff($localeDirs, $configLocales) ? 'warn' : 'ok', array_diff($localeDirs, $configLocales) ? 'folders not in config: ' . implode(', ', array_diff($localeDirs, $configLocales)) : 'folders match detected config locales');
$add('package.json', $package ? 'ok' : 'missing', $package ? 'found' : 'not found');
$scripts = $package['scripts'] ?? [];
$add('frontend build script', isset($scripts['prod']) || isset($scripts['build']) ? 'ok' : 'warn', implode(', ', array_keys($scripts)));
$add('build_production', is_dir($buildProduction) ? 'ok' : 'missing', is_dir($buildProduction) ? 'generated output exists' : 'run build production');
$pagesWorkflow = false;
foreach ($workflows as $workflow) {
    $content = (string) file_get_contents($workflow);
    if (str_contains($content, 'deploy-pages') || str_contains($content, 'upload-pages-artifact')) {
        $pagesWorkflow = true;
        break;
    }
}
$add('GitHub Pages workflow', $pagesWorkflow ? 'ok' : 'warn', $pagesWorkflow ? 'Pages Actions workflow detected' : 'not detected');

$result = [
    'root' => $root,
    'docs_dir' => $docsDir,
    'docs_path' => 'source/' . $docsDir,
    'locales_from_dirs' => $localeDirs,
    'locales_from_config' => $configLocales,
    'docara_locked_version' => $docaraLockedVersion,
    'tracked_core_files' => $trackedCoreCount,
    'checks' => $checks,
];

if (isset($options['json'])) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(0);
}

echo "Docara doctor: {$root}\n";
echo "DOCS_DIR: {$docsDir}\n\n";
foreach ($checks as $check) {
    printf("%-24s %-8s %s\n", $check['name'], strtoupper($check['status']), $check['detail']);
}

exit(0);
