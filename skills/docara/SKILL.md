---
name: docara
description: Build, configure, verify, publish, migrate, and maintain static documentation and landing sites with SIMAI Docara 2 using Markdown, validated JSON, Simai Framework, and the PHP-only CLI. Coordinate substantial content work with $docs, public search contracts with $seo, acceptance with $tester, and live deployment with $ops.
metadata:
  short-description: "Docara 2 documentation sites"
---

# Docara

Use this skill for Docara 2 project mechanics. Docara has one project model:
Markdown content under `content/<locale>`, validated JSON configuration and a
PHP-only deterministic build rendered with Simai Framework.

Before cross-domain work, read
[rules/skill-mesh-balance.md](./rules/skill-mesh-balance.md). For substantial
tasks, use the repo-local Mirai Graph runtime as the machine-readable index,
then return to this source for methodology and safe-write boundaries.

## Project contract

- Site settings: `docara.json`.
- Framework revisions: `simai-framework.lock.json`.
- Content: `content/<locale>/**/*.md`.
- Inherited section settings: `section.json`.
- Optional page settings: `<page>.page.json`.
- Redirects: `redirects.json`.
- Project assets: `assets/`.
- Output: `build_<environment>`; generated `.docara` and `build_*` are not
  authoring surfaces.

Read [references/project-model.md](./references/project-model.md) for setup and
[references/authoring-rules.md](./references/authoring-rules.md) for content.

## Workflow

1. Inspect `docara.json`, the locale registry, Framework lock, content tree and
   working tree before editing.
2. Initialize only an empty target with `php vendor/bin/docara init [path]`.
   Use `init --update` only after reviewing its ownership contract and diff.
3. Make the smallest complete Markdown/JSON/component change.
4. Build and verify:

   ```bash
   php vendor/bin/docara build production
   php vendor/bin/docara verify-static build_production
   ```

5. For local UI checks, serve the verified bytes over HTTP:

   ```bash
   php vendor/bin/docara serve production --host=127.0.0.1 --port=8000 --no-build
   ```

6. Report the exact source revision, output, verification evidence and any
   remaining publication or owner gate.

## Safety rules

- Never edit generated `build_*` or `.docara` files.
- Never treat `init` as an in-place converter for a pre-Docara-2 project.
- Before `init --update`, preserve authored content/settings and verify the
  engine-owned versus project-owned file contract.
- Keep secrets outside the project format; Docara does not require `.env`.
- Do not replace immutable Framework revisions with branches or moving URLs.
- Do not bypass schema, template, component-prop, path or static verification.
- Use backup, rollback and `$ops` before live deployment.

## Authoring and components

- Ordinary Markdown pages do not need a JSON sidecar.
- Use `section.json` only for inherited directory behavior and
  `<page>.page.json` only for page-specific behavior.
- Prefer native Markdown, then registered Docara components, then admitted
  `ui.*` Smart components. Do not invent product CSS when a Framework utility
  or component already expresses the design.
- Treat the generated `/components/catalog/` for the exact build as the
  authoritative component inventory.
- Add locale UI strings to language packs; do not embed one language in layout,
  region or component manifests.

## Publication

GitHub Pages or another static host publishes `build_production` only after
`verify-static` succeeds. Read
[references/github-pages.md](./references/github-pages.md) for Pages. Public
release, package publication and production deployment are separate gated
operations.
