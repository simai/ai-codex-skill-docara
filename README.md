# ai-codex-skill-docara

Codex skill for creating, configuring, publishing, and translating documentation sites based on SIMAI Docara.

## Skill

The skill lives in:

```text
skills/docara/
```

Use it as `$docara` when working with Docara documentation repositories.

## What It Covers

- Docara setup and repair.
- Publishable documentation authoring rules.
- GitHub Pages publication through Actions artifact deployment.
- GitHub project Pages path-prefix handling for `https://owner.github.io/repo/`.
- Translation drift tracking and AI-assisted translation workflow.
- Smoke scripts for repository inspection, starter setup, Pages workflow generation, and translation state.
- Markdown import into a contained Docara subproject for existing repositories.

## Useful Commands

Validate the skill:

```bash
python3 /Users/rim/.codex/skills/.system/skill-creator/scripts/quick_validate.py skills/docara
```

Run smoke tests:

```bash
bash tests/smoke.sh
```

Inspect a Docara project:

```bash
php skills/docara/scripts/docara-doctor.php --root=. --json
```

Prepare starter files in dry-run mode:

```bash
php skills/docara/scripts/prepare-docara-project.php --root=. --docs-dir=docs --locale=en
```

Import existing Markdown docs:

```bash
php skills/docara/scripts/import-markdown-docs.php --input=/path/to/repo --output=/path/to/repo/docara --locale=en --title="Project Documentation"
```

Generate a GitHub Pages workflow:

```bash
python3 skills/docara/scripts/create-github-pages-workflow.py --root=. --workflow=.github/workflows/docara-pages.yml
```

For a contained `docara/` subproject:

```bash
python3 skills/docara/scripts/create-github-pages-workflow.py --root=/path/to/repo --docara-dir=docara --workflow=/path/to/repo/.github/workflows/docara-pages.yml
```

For project Pages, keep Docara `baseUrl` environment-driven in the target project:

```php
'baseUrl' => rtrim((string) (getenv('DOCARA_BASE_URL') ?: ''), '/'),
```

## Repository Hygiene

- Real secrets belong in root `.env`; this repository does not need committed secrets.
- `source/` is local working material and is ignored.
- Release-visible changes are tracked in `CHANGELOG.md`.
