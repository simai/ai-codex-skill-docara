# Developer and AI SDK, preview, test, and QA

## Contents

- [Read-only discovery](#read-only-discovery)
- [Hash-bound scaffolding](#hash-bound-scaffolding)
- [Source-backed documentation tracking](#source-backed-documentation-tracking)
- [Validation and tests](#validation-and-tests)
- [Preview](#preview)
- [Structured QA](#structured-qa)
- [Optional MCP adapter](#optional-mcp-adapter)

The SDK delegates to production registries, schemas, the Smart Gateway,
composition, and `PageBuilder`. It is not a parallel implementation path.

## Read-only discovery

Run from an initialized project:

```bash
php vendor/bin/docara capabilities --json
php vendor/bin/docara doctor --json
php vendor/bin/docara list smart --json
php vendor/bin/docara list page --json
php vendor/bin/docara list source --json
php vendor/bin/docara list layout --json
php vendor/bin/docara inspect smart ui.alert --json
php vendor/bin/docara inspect layout docara.docs --json
php vendor/bin/docara inspect page /en/guide/ --json
php vendor/bin/docara inspect source simai-framework:component.buttons --json
php vendor/bin/docara schema smart --json
php vendor/bin/docara schema authoring --json
php vendor/bin/docara schema documentation-source --json
php vendor/bin/docara schema documentation-tracking --json
php vendor/bin/docara atlas --json
```

Treat `capabilities` as the package-owned compatibility handshake. It derives
the exact installed version/revision, command definitions, schemas, SDK types,
receipts, tracking and lifecycle support from the product runtime. Do not copy
that catalogue into the skill or infer a newer command from another project.

Available discovery kinds depend on the exact package and include page, Smart,
binding, layout, view, section, block, provider, fixture, state, and schema
surfaces. Human and `--json` outputs project one operation result with stable
diagnostics, ownership, provenance, and suggestions.

Use discovery before writing component/design IDs or props. Do not derive an
authoring contract from rendered HTML alone.

`inspect page` returns the physical source, public route, locale, front matter,
effective settings with provenance, optional authoring profile, component
`docs_ref` relations, examples, links, translation relation, project lock
descriptors, revisions, hashes, and diagnostics in
`docara.page_inspection.v1`.

## Hash-bound scaffolding

Create a review plan first:

```bash
php vendor/bin/docara scaffold smart project.notice \
  --dry-run --json
php vendor/bin/docara scaffold design project.docs \
  --dry-run --json
php vendor/bin/docara scaffold page guides/new-page \
  --locale=en --title="New page" --profile=how_to --dry-run --json
```

Review target paths, contents, input hashes, and `plan_id`. Apply only that
unchanged plan:

```bash
php vendor/bin/docara scaffold --apply=<exact-plan-sha256> --json
```

Any changed input, namespace, config, target, or hash makes the plan stale.
Smart/design scaffolding is create-only inside its owned roots. Page
scaffolding creates only an absent draft Markdown file inside the selected
locale content root. Source-aware page scaffolding may also create an absent
reusable example under `examples/`, but only from a complete source-owned
template; it never invents markup, parameters or states. It cannot overwrite a
page/example or write to engine, locks, build output, or external paths. Edit
existing Markdown directly, then validate, build and verify it.

```bash
php vendor/bin/docara scaffold page components/new-component \
  --source=simai-framework --entity=component.new-component \
  --locale=ru --profile=reference --dry-run --json
```

## Source-backed documentation tracking

When `documentation_tracking` is enabled, use the source contract as the
machine-readable work queue for the configured base locale:

```bash
php vendor/bin/docara documentation status --json
php vendor/bin/docara documentation status \
  --source=simai-framework --kind=component --status=changed --json
php vendor/bin/docara validate source simai-framework:component.buttons --json
```

Statuses are `current`, `new`, `changed`, `missing`, `missing_example`,
`unverified`, `orphan`, and `excluded`. A compatibility-adapter diagnostic
means the pinned source predates the neutral contract and status has limited
public-surface precision; do not silently accept ambiguous mappings.

For `new` or `changed`, inspect the exact entity and page, update Markdown and
reusable examples while preserving public classes, parameters, states, links
and technical structure, then validate/build/verify. Accept only a reviewed,
unambiguous relation:

```bash
php vendor/bin/docara documentation accept \
  --source=simai-framework --key=component.buttons \
  --route=/ru/components/buttons/ \
  --example=default=components/buttons/basic \
  --review=ai_verified --dry-run --json
php vendor/bin/docara documentation accept --apply=<exact-plan-sha256> --json
```

Any change to config, source contract, page, examples or previous lock makes
the plan stale. `status`, `validate` and build do not call AI/network or edit
Markdown, examples or `documentation.lock.json`. `human_reviewed` requires an
explicit human editorial review. Translation tracking remains independent.

## Validation and tests

```bash
php vendor/bin/docara validate project --json
php vendor/bin/docara validate page /en/guide/ --json
php vendor/bin/docara validate source simai-framework:component.buttons --json
php vendor/bin/docara validate smart project.notice --json
php vendor/bin/docara validate layout project.docs --json
php vendor/bin/docara test smart project.notice \
  --page=/en/project-demos/ --json
php vendor/bin/docara test layout project.docs \
  --page=/en/project-demos/ --json
```

Use a real page that resolves the artifact. Layout test/QA must fail when the
route does not select that layout; never substitute a convenient unrelated
page.

Tests are focused artifact evidence. Complete build, static verification, and
browser acceptance remain separate when the change affects a real site.

Page profile gaps and semantic checklist items are report-only. Invalid
Markdown, unsafe paths, missing assets/examples, broken technical links, or an
invalid authoring schema remain technical validation errors. Build warns about
authoring diagnostics but does not call AI, use the network, or edit sources.

## Preview

```bash
php vendor/bin/docara preview page \
  --page=/en/project-demos/
php vendor/bin/docara preview smart \
  --page=/en/project-demos/ --selector=project.notice
php vendor/bin/docara preview region \
  --page=/en/project-demos/ --selector=footer
php vendor/bin/docara preview layout \
  --page=/en/project-demos/
```

Add `--watch` for PHP-only bounded dependency watching. Preview publishes an
isolated production-path surface. It is not an accepted production receipt and
must not be deployed as a site build.

## Structured QA

Create an immutable draft plan:

```bash
php vendor/bin/docara qa smart project.notice \
  --page=/en/project-demos/ --dry-run --json
```

The plan binds the artifact, page, selector, desktop/mobile, light/dark, and
LTR/RTL scenarios as applicable. Optional browser tooling records reference
draft screenshots. Then:

```bash
php vendor/bin/docara qa \
  --finalize-reference=<exact-draft-plan-sha256> --json
php vendor/bin/docara qa \
  --verify=<exact-finalized-plan-sha256> --json
```

The finalized identity covers target, page, artifact, ordered scenarios,
screenshot paths, and every screenshot hash. Verification re-hashes reference
and candidate bytes and checks the full manifest seal. Do not accept a reported
zero-pixel diff without complete identity, screenshot, accessibility, console,
overflow, and scenario evidence.

Use `$tester` for the acceptance verdict and browser matrix.

## Optional MCP adapter

An exact package checkout may expose:

```bash
php tools/mcp-docara/server.php
```

The stdio adapter projects the same capabilities and application operations. It is read-only by
default. If explicitly launched with `--allow-writes`, apply still requires the
unchanged hash-bound plan and remains confined to the current project root.

MCP is optional development tooling. It is not part of the static site runtime,
does not grant write authority, and must not duplicate product validation or
rendering logic.
