# Changelog

## 0.2.2 - 2026-04-30

- Moved translation state to ignored `.docara-state/translate-state.php` by default.
- Added TODO reason metadata so agents can distinguish missing target files, unreviewed targets, changed sources, and previous TODO.
- Documented that stale locales must not be silenced with sync unless files were actually reviewed or translated.

## 0.2.1 - 2026-04-30

- Fixed generated Docara `.lang.php` files so settings, language, search, and actions menus receive complete UI labels.
- Added the missing localized defaults to both starter project preparation and Markdown import flows.

## 0.2.0 - 2026-04-30

- Validated `$docara` on the real `sitepack` repository using a contained `docara/` project.
- Added Markdown import for existing documentation repositories.
- Added contained-subproject GitHub Pages workflow generation with project-pages path prefix correction.
- Documented runtime lessons for ServBay PHP, Node 20, npm/webpack pinning, nested `source/` gitignore exceptions, GitHub Pages enablement, and partial translation state.

## 0.1.0 - 2026-04-29

- Added initial `$docara` Codex skill.
- Added Docara project model, authoring, GitHub Pages, and translation workflow references.
- Added scripts for Docara project inspection, starter preparation, Markdown import, translation state tracking, and GitHub Pages workflow generation.
- Added smoke tests for the bundled scripts.
