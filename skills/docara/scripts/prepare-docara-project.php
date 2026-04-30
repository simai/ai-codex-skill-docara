#!/usr/bin/env php
<?php

declare(strict_types=1);

$options = getopt('', ['root::', 'docs-dir::', 'locale::', 'write', 'help']);

if (isset($options['help'])) {
    echo "Usage: php prepare-docara-project.php [--root=.] [--docs-dir=docs] [--locale=en] [--write]\n";
    echo "Without --write the script prints planned setup actions only.\n";
    exit(0);
}

$root = realpath((string) ($options['root'] ?? getcwd()));
if ($root === false || ! is_dir($root)) {
    fwrite(STDERR, "Invalid root\n");
    exit(2);
}

$docsDir = trim((string) ($options['docs-dir'] ?? 'docs'), "/\\") ?: 'docs';
$locale = strtolower((string) ($options['locale'] ?? 'en'));
$write = isset($options['write']);
$actions = [];

function ensure_file(string $path, string $content, bool $write, array &$actions): void
{
    if (is_file($path)) {
        $actions[] = ['status' => 'exists', 'path' => $path];
        return;
    }
    $actions[] = ['status' => $write ? 'created' : 'would-create', 'path' => $path];
    if ($write) {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $content);
    }
}

function append_missing_lines(string $path, array $lines, bool $write, array &$actions): void
{
    $current = is_file($path) ? (string) file_get_contents($path) : '';
    $missing = [];
    foreach ($lines as $line) {
        if (! preg_match('/^' . preg_quote($line, '/') . '$/m', $current)) {
            $missing[] = $line;
        }
    }
    if (! $missing) {
        $actions[] = ['status' => 'exists', 'path' => $path, 'detail' => 'required lines present'];
        return;
    }
    $actions[] = ['status' => $write ? 'updated' : 'would-update', 'path' => $path, 'detail' => implode(', ', $missing)];
    if ($write) {
        $prefix = $current !== '' && ! str_ends_with($current, "\n") ? "\n" : '';
        file_put_contents($path, $current . $prefix . implode("\n", $missing) . "\n");
    }
}

function default_lang_php(string $locale): string
{
    if ($locale === 'ru') {
        $values = [
            'actions' => 'Действия',
            'dark' => 'Тёмная тема',
            'default' => 'Обычный',
            'search' => 'Поиск',
            'notFound' => 'Ничего не найдено',
            'settings' => 'Настройки',
            'on' => 'Вкл.',
            'off' => 'Выкл.',
            'edit article' => 'Редактировать статью',
            'report a bug' => 'Сообщить об ошибке',
            'wide' => 'Широкий режим',
            'text size' => 'Размер текста',
            'reduced' => 'Меньше',
            'increased' => 'Больше',
            'navigation' => 'Навигация',
        ];
    } else {
        $values = [
            'actions' => 'Actions',
            'dark' => 'Dark mode',
            'default' => 'Default',
            'search' => 'Search',
            'notFound' => 'Nothing found',
            'settings' => 'Settings',
            'on' => 'On',
            'off' => 'Off',
            'edit article' => 'Edit article',
            'report a bug' => 'Report a bug',
            'wide' => 'Wide layout',
            'text size' => 'Text size',
            'reduced' => 'Reduced',
            'increased' => 'Increased',
            'navigation' => 'Navigation',
        ];
    }

    return "<?php\nreturn " . var_export($values, true) . ";\n";
}

$composerPath = $root . '/composer.json';
if (is_file($composerPath)) {
    $composer = json_decode((string) file_get_contents($composerPath), true);
    $requiresDocara = isset($composer['require']['simai/docara']) || (($composer['name'] ?? null) === 'simai/docara');
    $actions[] = ['status' => $requiresDocara ? 'ok' : 'manual', 'path' => $composerPath, 'detail' => $requiresDocara ? 'simai/docara detected' : 'run composer require simai/docara'];
} else {
    $actions[] = ['status' => 'manual', 'path' => $composerPath, 'detail' => 'run composer require simai/docara'];
}

ensure_file($root . '/.env.example', "DOCS_DIR={$docsDir}\nAZURE_KEY=\nAZURE_REGION=\nAZURE_ENDPOINT=https://api.cognitive.microsofttranslator.com\n", $write, $actions);
append_missing_lines($root . '/.gitignore', ['.env', 'vendor/', 'node_modules/', 'build_*/', '.cache/', '.docara-state/', 'source/assets/build/', 'source/docs/.translate.php', 'output/', '*.bak', '*.bak_*', '*.codex-bak-*'], $write, $actions);

$localeRoot = $root . '/source/' . $docsDir . '/' . $locale;
ensure_file($localeRoot . '/.lang.php', default_lang_php($locale), $write, $actions);
ensure_file($localeRoot . '/.settings.php', "<?php\nreturn [\n    'title' => 'Documentation',\n    'showInMenu' => true,\n    'order' => 10,\n    'menu' => [\n        'index' => 'Overview',\n    ],\n];\n", $write, $actions);
ensure_file($localeRoot . '/index.md', "---\nextends: _core._layouts.documentation\nsection: content\ntitle: Documentation\ndescription: Documentation overview\n---\n\n# Documentation\n\nStart here.\n", $write, $actions);

foreach ($actions as $action) {
    $line = strtoupper($action['status']) . "\t" . $action['path'];
    if (! empty($action['detail'])) {
        $line .= "\t" . $action['detail'];
    }
    echo $line . PHP_EOL;
}

if (! $write) {
    echo "Dry run only. Re-run with --write to create missing starter files.\n";
}
