#!/usr/bin/env php
<?php

declare(strict_types=1);

$options = getopt('', [
    'docs-dir:',
    'state-file::',
    'source:',
    'targets::',
    'target::',
    'sync-targets::',
    'check-targets::',
    'force',
    'json',
    'print-locales',
    'print-todo',
    'print-todo-with-size',
    'print-orphans',
    'help',
]);

if (isset($options['help']) || empty($options['docs-dir']) || empty($options['source'])) {
    echo "Usage: php docara-translate-state.php --docs-dir=source/docs --source=en [--state-file=.docara-state/translate-state.php] [--targets=ru,de] [--target=ru] [--force] [--json] [--print-todo|--print-todo-with-size|--sync-targets=ru|--check-targets=ru|--print-locales|--print-orphans]\n";
    exit(isset($options['help']) ? 0 : 2);
}

$docsDir = rtrim((string) $options['docs-dir'], "/\\");
$source = strtolower((string) $options['source']);
$targets = [];
if (! empty($options['targets'])) {
    $targets = array_values(array_filter(array_map('trim', explode(',', strtolower((string) $options['targets'])))));
}
if (! empty($options['target'])) {
    $targets = array_values(array_unique(array_merge($targets, [strtolower((string) $options['target'])])));
}
if (! empty($options['sync-targets'])) {
    $targets = array_values(array_unique(array_merge($targets, array_filter(array_map('trim', explode(',', strtolower((string) $options['sync-targets'])))))));
}
if (! empty($options['check-targets'])) {
    $targets = array_values(array_unique(array_merge($targets, array_filter(array_map('trim', explode(',', strtolower((string) $options['check-targets'])))))));
}
$json = isset($options['json']);

if (! is_dir($docsDir)) {
    fwrite(STDERR, "Docs dir not found: {$docsDir}\n");
    exit(2);
}

$statePath = ! empty($options['state-file'])
    ? (string) $options['state-file']
    : default_state_path($docsDir);

function default_state_path(string $docsDir): string
{
    $normalized = str_replace('\\', '/', rtrim($docsDir, '/'));
    $marker = '/source/';
    $position = strpos($normalized, $marker);

    if ($position !== false) {
        $projectRoot = substr($normalized, 0, $position);
    } elseif (str_starts_with($normalized, 'source/')) {
        $projectRoot = '.';
    } else {
        $projectRoot = '.';
    }

    $projectRoot = rtrim($projectRoot, '/');
    return ($projectRoot === '' ? '.' : $projectRoot) . '/.docara-state/translate-state.php';
}

function load_state(string $path): array
{
    if (! is_file($path)) {
        return ['version' => 1, 'pairs' => []];
    }
    $state = include $path;
    return is_array($state) ? $state : ['version' => 1, 'pairs' => []];
}

function save_state(string $path, array $state): void
{
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $export = var_export($state, true);
    $export = preg_replace('/=>[ \t]+$/m', '=>', $export) ?? $export;
    file_put_contents($path, "<?php\nreturn {$export};\n");
}

function strip_updated_at(array $value): array
{
    unset($value['updated_at']);
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = strip_updated_at($item);
        }
    }
    return $value;
}

function is_translatable_file(string $path): bool
{
    $base = basename($path);
    if (str_ends_with($path, '.md')) {
        return true;
    }
    return $base === '.lang.php' || $base === '.settings.php';
}

function collect_files(string $root): array
{
    if (! is_dir($root)) {
        return [];
    }
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $path = $file->getPathname();
        if (! is_translatable_file($path)) {
            continue;
        }
        $relative = ltrim(str_replace('\\', '/', substr($path, strlen($root))), '/');
        $files[$relative] = [
            'path' => $path,
            'hash' => sha1_file($path),
            'size' => filesize($path) ?: 0,
        ];
    }
    ksort($files);
    return $files;
}

function print_locales(string $docsDir): void
{
    $locales = [];
    foreach (scandir($docsDir) ?: [] as $item) {
        if ($item === '.' || $item === '..' || str_starts_with($item, '.')) {
            continue;
        }
        if (is_dir($docsDir . '/' . $item)) {
            $locales[] = $item;
        }
    }
    sort($locales);
    foreach ($locales as $locale) {
        echo $locale . PHP_EOL;
    }
}

if (isset($options['print-locales'])) {
    if ($json) {
        $locales = [];
        foreach (scandir($docsDir) ?: [] as $item) {
            if ($item === '.' || $item === '..' || str_starts_with($item, '.')) {
                continue;
            }
            if (is_dir($docsDir . '/' . $item)) {
                $locales[] = $item;
            }
        }
        sort($locales);
        echo json_encode(['locales' => $locales], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        print_locales($docsDir);
    }
    exit(0);
}

if (! $targets) {
    fwrite(STDERR, "No targets provided. Use --targets=ru or --target=ru.\n");
    exit(2);
}

$sourceRoot = $docsDir . '/' . $source;
if (! is_dir($sourceRoot)) {
    fwrite(STDERR, "Source locale dir not found: {$sourceRoot}\n");
    exit(2);
}

$state = load_state($statePath);
$previousState = $state;
$now = date(DATE_ATOM);
$state['version'] = 1;
$state['docs_dir'] = $docsDir;
$sourceFiles = collect_files($sourceRoot);
$force = isset($options['force']);
$syncTargets = ! empty($options['sync-targets'])
    ? array_values(array_filter(array_map('trim', explode(',', strtolower((string) $options['sync-targets'])))))
    : [];
$checkTargets = ! empty($options['check-targets'])
    ? array_values(array_filter(array_map('trim', explode(',', strtolower((string) $options['check-targets'])))))
    : [];

foreach ($targets as $target) {
    if ($target === $source) {
        continue;
    }
    $pairKey = "{$source}:{$target}";
    $targetRoot = $docsDir . '/' . $target;
    $targetFiles = collect_files($targetRoot);
    $previousPair = $state['pairs'][$pairKey] ?? ['source' => $source, 'target' => $target, 'files' => []];
    $pair = $previousPair;
    $pair['source'] = $source;
    $pair['target'] = $target;
    $nextFiles = [];

    foreach ($sourceFiles as $relative => $info) {
        $previous = $pair['files'][$relative] ?? [];
        $targetInfo = $targetFiles[$relative] ?? null;
        $targetHash = $targetInfo['hash'] ?? null;
        $lastTargetHash = $previous['target_hash'] ?? null;
        $hasLocalChanges = $targetHash !== null && $lastTargetHash !== null && $targetHash !== $lastTargetHash;
        $sourceChanged = $force || (($previous['source_hash'] ?? null) !== $info['hash']);
        $targetMissing = $targetHash === null;
        $targetNotSynced = $targetHash !== null && $lastTargetHash === null;
        $needsTranslate = ! empty($previous['needs_translate']) || $sourceChanged || $targetMissing || $targetNotSynced;
        $reasons = [];
        if (! empty($previous['needs_translate'])) {
            $reasons[] = 'previous_todo';
        }
        if ($sourceChanged) {
            $reasons[] = 'source_changed';
        }
        if ($targetMissing) {
            $reasons[] = 'target_missing';
        }
        if ($targetNotSynced) {
            $reasons[] = 'target_not_synced';
        }

        if (in_array($target, $syncTargets, true) && $targetHash !== null) {
            $needsTranslate = false;
            $hasLocalChanges = false;
            $lastTargetHash = $targetHash;
            $reasons = [];
        }

        $entry = [
            'source_hash' => $info['hash'],
            'source_size' => $info['size'],
            'target_hash' => $targetHash ?? $lastTargetHash,
            'target_size' => $targetInfo['size'] ?? null,
            'needs_translate' => $needsTranslate,
            'reasons' => $needsTranslate ? $reasons : [],
            'has_local_changes' => $hasLocalChanges,
            'source_path' => "{$source}/{$relative}",
            'target_path' => "{$target}/{$relative}",
        ];
        $entry['updated_at'] = strip_updated_at($previous) === $entry
            ? ($previous['updated_at'] ?? $now)
            : $now;
        $nextFiles[$relative] = $entry;
    }

    $orphans = [];
    foreach ($targetFiles as $relative => $info) {
        if (! isset($sourceFiles[$relative])) {
            $orphans[$relative] = [
                'target_hash' => $info['hash'],
                'target_size' => $info['size'],
                'target_path' => "{$target}/{$relative}",
            ];
        }
    }
    ksort($orphans);
    $pair['files'] = $nextFiles;
    $pair['orphans'] = $orphans;
    $pair['updated_at'] = strip_updated_at($previousPair) === strip_updated_at($pair)
        ? ($previousPair['updated_at'] ?? $now)
        : $now;
    $state['pairs'][$pairKey] = $pair;
}

$state['updated_at'] = strip_updated_at($previousState) === strip_updated_at($state)
    ? ($previousState['updated_at'] ?? $now)
    : $now;
save_state($statePath, $state);

function selected_pairs(array $state, array $targets, string $source): array
{
    $pairs = [];
    foreach ($targets as $target) {
        $key = "{$source}:{$target}";
        if (isset($state['pairs'][$key])) {
            $pairs[$key] = $state['pairs'][$key];
        }
    }
    return $pairs;
}

$pairs = selected_pairs($state, $targets, $source);

function check_php_file(string $path): ?string
{
    $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1';
    exec($cmd, $out, $code);
    if ($code !== 0) {
        return trim(implode("\n", $out));
    }
    return null;
}

function check_markdown_file(string $path): array
{
    $errors = [];
    $text = (string) file_get_contents($path);
    if (preg_match('/^---\R/s', $text)) {
        $frontMatterEnds = preg_match_all('/^---\s*$/m', $text);
        if ($frontMatterEnds < 2) {
            $errors[] = 'front matter is opened but not closed';
        }
    }
    preg_match_all('/^```/m', $text, $fences);
    if ((count($fences[0]) % 2) !== 0) {
        $errors[] = 'unbalanced fenced code blocks';
    }
    foreach (preg_split('/\R/', $text) ?: [] as $lineNumber => $line) {
        if (str_starts_with(trim($line), '|') && substr_count($line, '|') < 2) {
            $errors[] = 'suspicious markdown table row at line ' . ($lineNumber + 1);
        }
    }
    return $errors;
}

function check_target_files(string $docsDir, array $pairs, array $checkTargets): array
{
    $issues = [];
    foreach ($pairs as $pair) {
        if ($checkTargets && ! in_array($pair['target'], $checkTargets, true)) {
            continue;
        }
        foreach ($pair['files'] as $relative => $entry) {
            $targetPath = $docsDir . '/' . $pair['target'] . '/' . $relative;
            if (! is_file($targetPath)) {
                if (empty($entry['needs_translate'])) {
                    $issues[] = ['target' => $pair['target'], 'file' => $relative, 'type' => 'missing', 'message' => 'target file is missing'];
                }
                continue;
            }
            if (str_ends_with($relative, '.php')) {
                $error = check_php_file($targetPath);
                if ($error !== null) {
                    $issues[] = ['target' => $pair['target'], 'file' => $relative, 'type' => 'php', 'message' => $error];
                }
            }
            if (str_ends_with($relative, '.md')) {
                foreach (check_markdown_file($targetPath) as $error) {
                    $issues[] = ['target' => $pair['target'], 'file' => $relative, 'type' => 'markdown', 'message' => $error];
                }
            }
        }
    }
    return $issues;
}

if ($checkTargets) {
    $issues = check_target_files($docsDir, $pairs, $checkTargets);
    if ($json) {
        echo json_encode(['issues' => $issues], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        if (! $issues) {
            echo "No target check issues\n";
        }
        foreach ($issues as $issue) {
            echo $issue['target'] . "\t" . $issue['file'] . "\t" . $issue['type'] . "\t" . $issue['message'] . PHP_EOL;
        }
    }
    exit($issues ? 1 : 0);
}

if (isset($options['print-orphans'])) {
    if ($json) {
        $orphans = [];
        foreach ($pairs as $pair) {
            foreach ($pair['orphans'] ?? [] as $relative => $entry) {
                $orphans[] = ['target' => $pair['target'], 'file' => $relative, 'size' => $entry['target_size']];
            }
        }
        echo json_encode(['orphans' => $orphans], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(0);
    }
    foreach ($pairs as $pair) {
        foreach ($pair['orphans'] ?? [] as $relative => $entry) {
            echo $pair['target'] . "\t" . $relative . "\t" . $entry['target_size'] . PHP_EOL;
        }
    }
    exit(0);
}

if (isset($options['print-todo']) || isset($options['print-todo-with-size'])) {
    if ($json) {
        $todo = [];
        foreach ($pairs as $pair) {
            foreach ($pair['files'] as $relative => $entry) {
                if (empty($entry['needs_translate'])) {
                    continue;
                }
                $todo[] = [
                    'target' => $pair['target'],
                    'file' => $relative,
                    'source_size' => $entry['source_size'],
                    'target_size' => $entry['target_size'] ?? null,
                    'reasons' => $entry['reasons'] ?? [],
                    'has_local_changes' => ! empty($entry['has_local_changes']),
                ];
            }
        }
        echo json_encode(['todo' => $todo], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(0);
    }
    foreach ($pairs as $pair) {
        foreach ($pair['files'] as $relative => $entry) {
            if (empty($entry['needs_translate'])) {
                continue;
            }
            $flags = [];
            if (! empty($entry['has_local_changes'])) {
                $flags[] = 'has_local_changes';
            }
            if (isset($options['print-todo-with-size'])) {
                echo $pair['target'] . "\t" . $relative . "\t" . $entry['source_size'] . "\t" . implode(',', $flags) . PHP_EOL;
            } else {
                echo $pair['target'] . "\t" . $relative . (count($flags) ? "\t" . implode(',', $flags) : '') . PHP_EOL;
            }
        }
    }
    exit(0);
}

$summary = [];
foreach ($pairs as $pair) {
    $todo = 0;
    $local = 0;
    foreach ($pair['files'] as $entry) {
        $todo += ! empty($entry['needs_translate']) ? 1 : 0;
        $local += ! empty($entry['has_local_changes']) ? 1 : 0;
    }
    $summary[] = $pair['target'] . ": todo={$todo}, local_changes={$local}, orphans=" . count($pair['orphans'] ?? []);
}
if ($json) {
    $jsonSummary = [];
    foreach ($pairs as $pair) {
        $todo = 0;
        $local = 0;
        foreach ($pair['files'] as $entry) {
            $todo += ! empty($entry['needs_translate']) ? 1 : 0;
            $local += ! empty($entry['has_local_changes']) ? 1 : 0;
        }
        $jsonSummary[] = ['target' => $pair['target'], 'todo' => $todo, 'local_changes' => $local, 'orphans' => count($pair['orphans'] ?? [])];
    }
    echo json_encode(['summary' => $jsonSummary], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    echo implode(PHP_EOL, $summary) . PHP_EOL;
}
