---
name: docara
description: Build, configure, publish, and maintain documentation sites with SIMAI Docara. Use when the user invokes $docara or asks to initialize/update Docara, prepare Docara source structure, configure layout/locales/menus/front matter, publish a static documentation site through GitHub Pages, or translate and synchronize multilingual Docara docs. For documentation methodology, structure, writing, screenshots, and content audit, coordinate with $docs.
metadata:
  short-description: "Docara documentation sites"
---

# Docara

Use this skill to turn repository documentation into a ready-to-build Docara site, keep the site maintainable, and publish it as static documentation. It owns the Docara project and publication mechanics, not the whole technical-writing process.

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
   - Branded color theme from one base color: read `references/theme-workflow.md`.
3. Make the smallest complete change: config, docs tree, workflow, or translation batch.
4. Verify with the strongest available local checks:
   ```bash
   composer validate --no-check-publish
   php vendor/bin/docara init --update
   php <skill>/scripts/docara-apply-branding.php --root=. --title='Project Name' --write
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
- If frontend dependencies are pinned after testing, mirror the pin in `source/_core/package.json` as well as root `package.json` before using `npm ci` in CI. `docara init --update` can refresh root frontend files from `source/_core`, and a package/lock mismatch will stop GitHub Pages before the build.
- For GitHub Pages, prefer Actions artifact deployment from `build_production`; use root `/docs` only when the user explicitly wants committed build output.
- After `docara init --update` or Markdown import, apply project branding before publication. Do not leave the sample `simai ui` logo, do not append generic `Docs` unless it is part of the product name, and remove the floating theme-builder palette for public documentation:
  ```bash
  php <skill>/scripts/docara-apply-branding.php --root=. --title='Project Name' --write
  ```

## Authoring Rules

- If the documentation map, reader journeys, genre split, screenshots, or source content are missing or weak, use `$docs` first or in parallel. `$docara` should not hide a weak documentation method behind a working static-site build.
- Every navigable section should have a `.settings.php` with `title`, `order`, `showInMenu`, and `menu` where needed.
- Markdown pages should include front matter with `extends: _core._layouts.documentation`, `section: content`, `title`, and `description`.
- Keep slugs, paths, anchors, front matter keys, PHP array keys, fenced code, inline code, and URLs stable unless the user asks for structural changes.
- Documentation must be publishable, not just readable in chat: update menus, language packs, links, and build config together.

## SEO Contract Rules

- For public documentation sites, product docs, generated static output, `baseUrl`, canonical/static paths, sitemap, menu/breadcrumb structure, or SEO Contract implementation, treat `$seo` as the contract owner.
- Implement SEO Contract fields through Docara-native documentation structure: front matter, `.settings.php`, menus, `baseUrl`, build config, static paths, section IA, titles, descriptions, breadcrumbs, and generated output.
- Do not silently change SEO Contract decisions. If a contract conflicts with Docara build paths, GitHub Pages project paths, translation state, or versioned docs policy, report a blocker to `$seo` with the smallest safe contract adjustment.
- Return the changed docs paths, config keys, generated output path, and target URLs for `$seo` review and `$tester` search-visibility acceptance.

## Theme Rules

Use script-generated CSS variable overrides for branded Docara themes. Do not rely on the floating SIMAI UI theme-builder icon as production configuration.

```bash
php <skill>/scripts/docara-theme-vars.php --root=. --seed=#E81123 --install
```

The script writes `source/_core/_assets/css/_theme.generated.scss` and imports it from `main.scss`, so the theme is compiled into `assets/build/css/main.css` and then included in `build_production`.

## Translation Rules

Use script-managed state for deterministic drift tracking, then use the model only for the actual translation text.

Common commands:

```bash
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --print-todo-with-size --target=ru
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --targets=ru --sync-targets=ru
php <skill>/scripts/docara-translate-state.php --docs-dir=source/docs --source=en --print-locales
```

The state file is local operational metadata. Keep it in the default `.docara-state/translate-state.php` or pass `--state-file`; do not commit `.docara-state/` or legacy `source/docs/.translate.php`. If TODO is non-zero, say the target locale is partial/stale rather than equivalent to the source. Read JSON TODO `reasons` before editing, and never use `--sync-targets` to silence files that were not actually reviewed or translated.

Translate only TODO files unless the user requests a full refresh. If a target file has local changes, stop and ask whether to overwrite, keep a copy, or skip.

## Bundled Scripts

- `scripts/docara-doctor.php` checks Docara installation, docs tree, locales, build output, and GitHub Pages workflow hints.
- `scripts/docara-apply-branding.php` installs project header branding and removes the public SIMAI UI theme-builder demo by default.
- `scripts/import-markdown-docs.php` imports existing Markdown documentation into `source/docs/<locale>` with Docara front matter and `.settings.php` menus.
- `scripts/docara-translate-state.php` scans source/target docs, prints TODO files, detects target drift and orphans, and syncs translation state.
- `scripts/docara-theme-vars.php` generates SIMAI UI color variable overrides from one seed color and can install the generated SCSS into Docara's frontend build.
- `scripts/prepare-docara-project.php` prepares safe starter files for a Docara project in dry-run or write mode.
- `scripts/create-github-pages-workflow.py` writes a GitHub Pages workflow that builds Docara and deploys `build_production`.

## Publication Decision

Use this default decision:

- Same repo + GitHub Actions Pages: best default for most projects.
- Same repo + root `/docs`: acceptable only when the repository intentionally commits built static files.
- Separate repo: use for separate access control, separate public/private policy, independent lifecycle, or a dedicated documentation product.

## Placement Decision

- Existing product/tool repository with current root docs: prefer a contained `docara/` subproject. Keep Docara source in `docara/source/docs`, build output in `docara/build_production`, and publish that output.
- Dedicated documentation-only repository: root-level Docara is acceptable and simpler.
- If parent `.gitignore` ignores `source`, add explicit exceptions for `docara/source/` when the Docara subproject must be committed.

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
