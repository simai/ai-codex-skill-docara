---
name: docara
description: Build, configure, publish, and maintain documentation sites with SIMAI Docara. Use when the user invokes $docara or asks to create project documentation, prepare Docara content, initialize or update Docara, configure layout/locales/menus, publish a static documentation site through GitHub Pages, or translate and synchronize multilingual Docara docs.
metadata:
  short-description: "Docara documentation sites"
---

# Docara

Use this skill to turn repository documentation into a ready-to-build Docara site, keep the site maintainable, and publish it as static documentation.

Docara is a PHP 8.2 static documentation generator based on Jigsaw. Its project model is:

- Source: `source/<DOCS_DIR>/<locale>`, usually `source/docs/en`.
- Config: `.env`, `config.php`, `translate.config.php`, `package.json`.
- Build: `php vendor/bin/docara build production`.
- Output: `build_production` unless `config.production.php` overrides `build.destination`.

## Workflow

1. Inspect the repository before changing anything:
   ```bash
   php <skill>/scripts/docara-doctor.php --root=. --json
   ```
2. Choose the nearest task:
   - New or repaired Docara setup: read `references/project-model.md`.
   - Writing or restructuring content: read `references/authoring-rules.md`.
   - GitHub Pages publication: read `references/github-pages.md`.
   - Translation and drift management: read `references/translation-workflow.md`.
3. Make the smallest complete change: config, docs tree, workflow, or translation batch.
4. Verify with the strongest available local checks:
   ```bash
   composer validate --no-check-publish
   php vendor/bin/docara init --update
   yarn prod || npm run prod
   php vendor/bin/docara build production
   ```
   Adjust commands to the repository's package manager and installed state.
5. Report the exact publication path, build output, remaining blockers, and any manual GitHub Pages setting the user must enable.

## Setup Rules

- Keep real secrets in root `.env`; use `.env.example` only for safe placeholders.
- Prefer `DOCS_DIR=docs`; do not confuse root `/docs` publication output with Docara source `source/docs`.
- Do not overwrite existing `source/docs` content during `docara init`; use `--update` and inspect diffs.
- Treat `source/_core` as generated/customizable project files. Avoid broad edits unless the task is core layout customization.
- For GitHub Pages, prefer Actions artifact deployment from `build_production`; use root `/docs` only when the user explicitly wants committed build output.

## Authoring Rules

- Every navigable section should have a `.settings.php` with `title`, `order`, `showInMenu`, and `menu` where needed.
- Markdown pages should include front matter with `extends: _core._layouts.documentation`, `section: content`, `title`, and `description`.
- Keep slugs, paths, anchors, front matter keys, PHP array keys, fenced code, inline code, and URLs stable unless the user asks for structural changes.
- Documentation must be publishable, not just readable in chat: update menus, language packs, links, and build config together.

## Translation Rules

Use script-managed state for deterministic drift tracking, then use the model only for the actual translation text.

Common commands:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --print-todo-with-size --target=ru
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --sync-targets=ru
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --print-locales
```

Translate only TODO files unless the user requests a full refresh. If a target file has local changes, stop and ask whether to overwrite, keep a copy, or skip.

## Bundled Scripts

- `scripts/docara-doctor.php` checks Docara installation, docs tree, locales, build output, and GitHub Pages workflow hints.
- `scripts/docara-translate-state.php` scans source/target docs, prints TODO files, detects target drift and orphans, and syncs translation state.
- `scripts/prepare-docara-project.php` prepares safe starter files for a Docara project in dry-run or write mode.
- `scripts/create-github-pages-workflow.py` writes a GitHub Pages workflow that builds Docara and deploys `build_production`.

## Publication Decision

Use this default decision:

- Same repo + GitHub Actions Pages: best default for most projects.
- Same repo + root `/docs`: acceptable only when the repository intentionally commits built static files.
- Separate repo: use for separate access control, separate public/private policy, independent lifecycle, or a dedicated documentation product.

## Prompt Examples

Create or repair documentation:

```text
$docara
Подготовь Docara-документацию для этого репозитория: проверь структуру, создай стартовые файлы, настрой меню и добейся успешной production-сборки.
```

Publish through GitHub Pages:

```text
$docara
Настрой публикацию этой Docara-документации через GitHub Pages Actions без коммита build_production.
```

Translate documentation:

```text
$docara
Обнови перевод документации en -> ru. Сначала покажи TODO и локальные изменения, затем переводи батчами с синхронизацией state.
```
