# Docara Translation Workflow

Use this when creating, updating, or auditing multilingual Docara content.

## Model

Translation has two layers:

- Deterministic state script: finds changed files, TODO, target drift, and orphaned target files.
- AI/manual translation: edits the target files while preserving Docara structure.

Do not use AI to decide which files are stale when the script can compute it.

The translation state is operational metadata, not documentation content. By default the state script writes to `.docara-state/translate-state.php` at the Docara project root. Keep `.docara-state/` ignored and do not commit old `source/docs/.translate.php` state files. If a project needs another location, pass `--state-file=<path>` explicitly and add that path to `.gitignore`.

## Commands

List locales:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --print-locales
```

Create or refresh TODO:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --print-todo-with-size --target=ru
```

Use JSON when automating a batch:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --print-todo-with-size --target=ru --json
```

Before editing translations, inspect both summary and TODO:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --json
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --print-todo-with-size --target=ru --json
```

If TODO is not zero, the target locale is not current. Do not claim that the locale matches the source until TODO is zero and `--check-targets` passes.

Read TODO reasons before choosing the next action:

- `target_missing`: create and translate the target file.
- `target_not_synced`: existing target has not yet been confirmed against the current source; inspect or translate before syncing.
- `source_changed`: source changed after the last sync; update the target.
- `previous_todo`: the file was already known to need translation.

Only run `--sync-targets=<locale>` after the target file was actually reviewed or translated. Never use sync as a way to silence a stale locale.

Force full re-check:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --force --print-todo-with-size --target=ru
```

Sync after translation:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --sync-targets=ru
```

Check target syntax and lightweight Markdown integrity after translation:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --check-targets=ru
```

Find orphan target files:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --print-orphans --target=ru
```

## Batch Strategy

- For normal work, process one TODO file, sync, refresh TODO, then continue.
- If user asks "translate all" or "продолжай до конца", process all TODO until done or until local target drift appears.
- For batch work, use `--print-todo-with-size`; group about 20-30 KB source text or 5-10 files.
- `.settings.php` and `.lang.php` are small service files and may be grouped.

## Implementation Checklist

Use this order when adding translation support to a project:

1. Ensure `.docara-state/` and legacy `source/docs/.translate.php` are ignored.
2. Run summary JSON and TODO JSON before editing.
3. Fix service files first: `.lang.php` and `.settings.php`.
4. Translate missing target files next, starting with section `index.md` pages so navigation is not misleading.
5. Translate or review changed existing pages in size-aware batches.
6. After each batch, run `--check-targets=<locale>`, build production, and browser-smoke the language switcher, search, settings menu, and representative pages.
7. Run `--sync-targets=<locale>` only for files that were reviewed or translated in that batch.
8. Stop when TODO is zero, target checks pass, and the build/browser smoke pass. If TODO remains, report the locale as partial.

## Local Change Guard

If TODO output marks `has_local_changes`, stop and ask the user:

- overwrite target file,
- keep a copy and overwrite,
- skip this file.

Do not silently overwrite target translations edited by a human.

## Translation Editing Rules

Markdown:

- Preserve front matter keys.
- Translate only human-facing front matter values such as `title`, `description`, `summary`.
- Preserve fenced code blocks, inline code, URLs, anchors, Blade directives, custom tags, and file paths.
- Preserve table structure and code indentation.

`.settings.php`:

- Translate `title` and `menu` values.
- Keep slugs, order, booleans, paths, and array keys intact.

`.lang.php`:

- Translate string values only.
- Keep array keys and structure intact.

## Locale Wiring

When adding a new locale:

1. Add target folder under `source/docs/<locale>` or let translation create it.
2. Update `config.php` `locales` map when the locale should appear in the language switcher immediately.
3. Update `translate.config.php` `languages` if using Docara's Azure translator.
4. Build and verify the language switcher.

For partial translations, sync only files that already exist in the target locale. The state script will keep the rest in TODO with `has_local_changes=false`, which is the expected state for incremental translation.
