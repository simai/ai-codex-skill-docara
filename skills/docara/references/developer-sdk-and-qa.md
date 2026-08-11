# Developer and AI SDK, preview, test, and QA

## Contents

- [Read-only discovery](#read-only-discovery)
- [Hash-bound scaffolding](#hash-bound-scaffolding)
- [Validation and tests](#validation-and-tests)
- [Preview](#preview)
- [Structured QA](#structured-qa)
- [Optional MCP adapter](#optional-mcp-adapter)

The SDK delegates to production registries, schemas, the Smart Gateway,
composition, and `PageBuilder`. It is not a parallel implementation path.

## Read-only discovery

Run from an initialized project:

```bash
php vendor/bin/docara doctor --json
php vendor/bin/docara list smart --json
php vendor/bin/docara list layout --json
php vendor/bin/docara inspect smart ui.alert --json
php vendor/bin/docara inspect layout docara.docs --json
php vendor/bin/docara schema smart --json
php vendor/bin/docara atlas --json
```

Available discovery kinds depend on the exact package and include Smart,
binding, layout, view, section, block, provider, fixture, state, and schema
surfaces. Human and `--json` outputs project one operation result with stable
diagnostics, ownership, provenance, and suggestions.

Use discovery before writing component/design IDs or props. Do not derive an
authoring contract from rendered HTML alone.

## Hash-bound scaffolding

Create a review plan first:

```bash
php vendor/bin/docara scaffold smart project.notice \
  --dry-run --json
php vendor/bin/docara scaffold design project.docs \
  --dry-run --json
```

Review target paths, contents, input hashes, and `plan_id`. Apply only that
unchanged plan:

```bash
php vendor/bin/docara scaffold --apply=<exact-plan-sha256> --json
```

Any changed input, namespace, config, target, or hash makes the plan stale.
Scaffolding is create-only inside allowed project `smart/` or `design/` roots.
It cannot overwrite an existing artifact or write to engine, content, assets,
lock, build output, or external paths.

## Validation and tests

```bash
php vendor/bin/docara validate project --json
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

The stdio adapter projects the same application operations. It is read-only by
default. If explicitly launched with `--allow-writes`, apply still requires the
unchanged hash-bound plan and remains confined to the current project root.

MCP is optional development tooling. It is not part of the static site runtime,
does not grant write authority, and must not duplicate product validation or
rendering logic.
