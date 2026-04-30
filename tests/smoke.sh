#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

fail() {
  echo "FAIL: $*" >&2
  exit 1
}

assert_contains() {
  local haystack="$1"
  local needle="$2"
  [[ "$haystack" == *"$needle"* ]] || fail "expected output to contain: $needle"
}

PROJECT="$TMP/project"
mkdir -p "$PROJECT/source/docs/en"

cat > "$PROJECT/composer.json" <<'JSON'
{
  "name": "simai/docara-smoke",
  "require": {
    "simai/docara": "^0.1"
  }
}
JSON

cat > "$PROJECT/.env" <<'ENV'
DOCS_DIR=docs
AZURE_KEY=
AZURE_REGION=
AZURE_ENDPOINT=https://api.cognitive.microsofttranslator.com
ENV

cat > "$PROJECT/config.php" <<'PHP'
<?php
return [
    'locales' => [
        'en' => 'English',
        'ru' => 'Русский',
    ],
    'defaultLocale' => 'en',
];
PHP

cat > "$PROJECT/package.json" <<'JSON'
{
  "scripts": {
    "prod": "echo build"
  }
}
JSON

cat > "$PROJECT/source/docs/en/.lang.php" <<'PHP'
<?php
return [
    'search' => 'Search',
];
PHP

cat > "$PROJECT/source/docs/en/.settings.php" <<'PHP'
<?php
return [
    'title' => 'Documentation',
    'showInMenu' => true,
    'order' => 10,
    'menu' => [
        'index' => 'Overview',
    ],
];
PHP

cat > "$PROJECT/source/docs/en/index.md" <<'MD'
---
extends: _core._layouts.documentation
section: content
title: Overview
description: Overview
---

# Overview

Use `code` safely.
MD

doctor="$(php "$ROOT/docara/scripts/docara-doctor.php" --root="$PROJECT" --json)"
assert_contains "$doctor" '"docs_dir": "docs"'
assert_contains "$doctor" '"locales_from_dirs":'

prepare_dry="$(php "$ROOT/docara/scripts/prepare-docara-project.php" --root="$PROJECT" --docs-dir=docs --locale=en)"
assert_contains "$prepare_dry" 'Dry run only'

php "$ROOT/docara/scripts/prepare-docara-project.php" --root="$PROJECT" --docs-dir=docs --locale=en --write >/dev/null
[[ -f "$PROJECT/.env.example" ]] || fail ".env.example was not created"
! grep -q '^source/$' "$PROJECT/.gitignore" || fail "Docara project .gitignore must not ignore source/"

todo_json="$(php "$ROOT/docara/scripts/docara-translate-state.php" --docs-dir="$PROJECT/source/docs" --source=en --targets=ru --print-todo-with-size --target=ru --json)"
assert_contains "$todo_json" '"file": "index.md"'

mkdir -p "$PROJECT/source/docs/ru"
cp "$PROJECT/source/docs/en/.lang.php" "$PROJECT/source/docs/ru/.lang.php"
cp "$PROJECT/source/docs/en/.settings.php" "$PROJECT/source/docs/ru/.settings.php"
cp "$PROJECT/source/docs/en/index.md" "$PROJECT/source/docs/ru/index.md"

php "$ROOT/docara/scripts/docara-translate-state.php" --docs-dir="$PROJECT/source/docs" --source=en --targets=ru --sync-targets=ru >/dev/null
todo_after_sync="$(php "$ROOT/docara/scripts/docara-translate-state.php" --docs-dir="$PROJECT/source/docs" --source=en --targets=ru --print-todo --target=ru)"
[[ -z "$todo_after_sync" ]] || fail "expected empty TODO after sync, got: $todo_after_sync"

check_after_sync="$(php "$ROOT/docara/scripts/docara-translate-state.php" --docs-dir="$PROJECT/source/docs" --source=en --targets=ru --check-targets=ru)"
assert_contains "$check_after_sync" 'No target check issues'

cat >> "$PROJECT/source/docs/en/index.md" <<'MD'

New source text.
MD

todo_after_change="$(php "$ROOT/docara/scripts/docara-translate-state.php" --docs-dir="$PROJECT/source/docs" --source=en --targets=ru --print-todo --target=ru)"
assert_contains "$todo_after_change" 'index.md'

python3 "$ROOT/docara/scripts/create-github-pages-workflow.py" --root="$PROJECT" --workflow="$PROJECT/.github/workflows/docara-pages.yml" >/dev/null
grep -q 'actions/deploy-pages@v4' "$PROJECT/.github/workflows/docara-pages.yml" || fail "Pages workflow missing deploy action"
grep -q 'DOCARA_BASE_URL' "$PROJECT/.github/workflows/docara-pages.yml" || fail "Pages workflow missing base URL env"
grep -q 'DOCARA_PAGES_PREFIX' "$PROJECT/.github/workflows/docara-pages.yml" || fail "Pages workflow missing project Pages prefix handling"
grep -q 'search-index_' "$PROJECT/.github/workflows/docara-pages.yml" || fail "Pages workflow missing search index prefix handling"

IMPORT_PROJECT="$TMP/import-project"
mkdir -p "$IMPORT_PROJECT/docs"
cat > "$IMPORT_PROJECT/README.md" <<'MD'
# Import Project
MD
cat > "$IMPORT_PROJECT/docs/setup.md" <<'MD'
# Setup
MD
import_dry="$(php "$ROOT/docara/scripts/import-markdown-docs.php" --input="$IMPORT_PROJECT" --output="$IMPORT_PROJECT/docara" --include=README.md,docs)"
assert_contains "$import_dry" 'WOULD_IMPORT'
php "$ROOT/docara/scripts/import-markdown-docs.php" --input="$IMPORT_PROJECT" --output="$IMPORT_PROJECT/docara" --include=README.md,docs --write >/dev/null
[[ -f "$IMPORT_PROJECT/docara/source/docs/en/index.md" ]] || fail "imported index.md missing"
[[ -f "$IMPORT_PROJECT/docara/source/docs/en/docs/.settings.php" ]] || fail "imported settings missing"
[[ -f "$IMPORT_PROJECT/docara/source/docs/en/docs/index.md" ]] || fail "imported section index missing"

echo "smoke ok"
