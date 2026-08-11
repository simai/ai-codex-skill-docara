# Mirai Graph Runtime Context: docara

- Task: `Federation 8.10.0 Docara quality after upstream architecture rebuild`
- Objects: 12
- Relations: 18
- Canonical writes: false

## Included Objects

- `skill.docara.core` (0.33): Routes current standalone Docara 2 project work across authoring, settings, components, compilation, QA, updates, and publication.
- `gate.docara.acceptance` (0.33): Requires exact-package validation, appropriate compilation, static verification, and proportionate visible QA before an outcome is accepted.
- `capability.docara.project-lifecycle` (0.18): Creates, adopts, and inspects standalone Docara projects while preserving project and package ownership.
- `capability.docara.settings-inheritance` (0.18): Changes site, section, page, branding, layout, reading, search, and locale settings through the validated inheritance model.
- `capability.docara.content-localization-navigation` (0.18): Authors and migrates Markdown, locales, routes, navigation, redirects, and translation state.
- `capability.docara.components-smart-design` (0.18): Uses native renderers, typed components, containers, design artifacts, and admitted Smart artifacts without bypassing contracts.
- `capability.docara.build-preview-verification` (0.18): Selects full or single-page compilation, verifies exact static bytes, and performs proportionate browser acceptance.
- `capability.docara.developer-sdk-qa` (0.18): Discovers registries, scaffolds hash-bound artifacts, validates, tests, previews, and records structured QA evidence.
- `capability.docara.update-release-publication` (0.18): Runs transactional engine updates and separates rollback, release readiness, package publication, and deployment.
- `capability.docara.security-troubleshooting` (0.18): Diagnoses failures while preserving path, ownership, lock, namespace, schema, hash, and static-output protections.
- `policy.docara.source-ownership` (0.18): Edits project-owned inputs and rejects generated build output or package-owned engine state as authoring sources.
- `policy.docara.lifecycle-separation` (0.18): Keeps build, static verification, browser acceptance, release, publication, and deployment as separate evidence states.

## Raw Source Refs

- `skills/docara/SKILL.md`
- `skills/docara/references/build-preview-and-verification.md`
- `skills/docara/references/developer-sdk-and-qa.md`
- `skills/docara/references/architecture-and-project-model.md`
- `skills/docara/references/settings-and-inheritance.md`
- `skills/docara/references/content-locales-and-navigation.md`
- `skills/docara/references/components-smart-and-design.md`
- `skills/docara/references/update-release-and-publication.md`
- `skills/docara/references/security-and-troubleshooting.md`

## Runtime Boundary

Graph context is routing/capability orientation only. Raw skill files remain authoritative.
