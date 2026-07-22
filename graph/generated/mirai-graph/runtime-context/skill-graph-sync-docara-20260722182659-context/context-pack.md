# Mirai Graph Runtime Context: docara

- Task: `Sync raw source changes for docara: capability, documentation, federation-impact, methodology, routing-impact, runtime`
- Objects: 9
- Relations: 20
- Canonical writes: false

## Included Objects

- `skill.docara.core` (0.87): Owns Docara documentation site setup, front matter, menu, locales, theme workflow, GitHub Pages and publication checks.
- `capability.docara.project-setup` (0.72): Prepare Docara project model and source structure.
- `policy.docara.docs-owner-boundary` (0.69): Docara publishes site structure; docs skill owns content method and quality.
- `policy.docara.publish-smoke` (0.69): Published Docara site needs config, links/assets and smoke checks before ready.
- `gate.docara.publish-readiness` (0.69): Blocks publish-ready status until project, menu/locales/theme and publication smoke are covered.
- `capability.docara.authoring-frontmatter` (0.54): Apply authoring rules, front matter and content import expectations.
- `capability.docara.menu-locales` (0.54): Configure menus, locales and translation workflow.
- `capability.docara.theme-branding` (0.54): Apply Docara theme variables and branding workflow.
- `capability.docara.publish-github-pages` (0.54): Prepare GitHub Pages workflow and publication checks.

## Raw Source Refs

- `skills/docara/SKILL.md`
- `skills/docara/references/project-model.md`
- `skills/docara/scripts/prepare-docara-project.php`
- `skills/docara/rules/skill-mesh-balance.md`
- `skills/docara/references/github-pages.md`
- `skills/docara/references/authoring-rules.md`
- `skills/docara/scripts/import-markdown-docs.php`
- `skills/docara/references/translation-workflow.md`
- `skills/docara/scripts/docara-translate-state.php`
- `skills/docara/references/theme-workflow.md`
- `skills/docara/scripts/docara-theme-vars.php`
- `skills/docara/scripts/create-github-pages-workflow.py`

## Runtime Boundary

Graph context is routing/capability orientation only. Raw skill files remain authoritative.
