# Build, preview, and verification

## Contents

- [Preflight](#preflight)
- [Complete build](#complete-build)
- [Single-page build](#single-page-build)
- [Atomic publication](#atomic-publication)
- [Static output](#static-output)
- [Static verification](#static-verification)
- [HTTP and preview](#http-and-preview)
- [Determinism](#determinism)

## Preflight

Run from the project root with the exact selected package:

```bash
git status --short
php vendor/bin/docara doctor --json
```

Resolve missing PHP/Composer/vendor state explicitly. Do not silently use a
different global Docara checkout. Build performs its own schema, ownership,
path, lock, registry, and input checks, but `doctor` gives an earlier actionable
diagnostic surface.

## Complete build

```bash
php vendor/bin/docara build production
```

The environment suffix controls `build_<environment>`; it does not select a
different content renderer.

At a high level, a complete build:

1. validates the project root, site config, schemas, lock, and providers;
2. discovers every declared locale and physical Markdown owner;
3. resolves inherited settings and provenance for every page;
4. compiles Markdown into typed Document IR and renders it through one registry;
5. derives outline, locale links, backlinks, catalogues, schemas, topology,
   menu, breadcrumbs, previous/next, search, redirects, shared-example assets,
   translation status, and asset plans;
6. composes layout regions and verifies main-document parity;
7. renders the publisher shell and writes all pages/assets/receipts into a
   candidate directory;
8. atomically promotes the candidate only after complete success.

Use a complete build after any structural, global, lock, registry, locale,
route, navigation, redirect, shared search/reading, or section-inheritance
change.

## Single-page build

```bash
php vendor/bin/docara build production --page=/en/guides/install/
```

Use only when:

- a complete build already exists and has accepted receipts;
- the route already exists in that build;
- build purpose, engine tree, and dependency tuple match;
- topology, search, backlinks, atlas, schemas, locks, and registries remain
  valid for the accepted complete build;
- the page is not a draft.

Docara copies the accepted output into an atomic candidate, recompiles the
selected route through the same `PageBuilder`, and preserves accepted global
projections. It does not make route creation, deletion, renaming, or a global
setting change safe.

## Atomic publication

Build never replaces the current result until the candidate is complete. It
temporarily moves the current output to a rollback directory, promotes the
candidate, and restores the previous output if promotion fails.

This is local build atomicity, not deployment authorization or a persistent
release rollback strategy.

## Static output

A production output normally contains:

```text
build_production/
├── index.html or locale root redirects
├── <route>/index.html
├── _docara/
│   ├── search-index.json
│   ├── search.js
│   ├── component-catalog.json
│   ├── framework/
│   └── vendor/simai-framework/runtime/<revision>/
└── .docara/
    ├── resolved-page-plans.json
    ├── redirects.json
    ├── backlinks.json
    ├── component-index.json
    ├── design-atlas.json
    ├── examples.json
    ├── schema-reference.json
    └── translation-status.json         # when tracking is enabled
```

Search files are emitted only when enabled. Assets are local and revisioned by
hash. The static site must not rely on runtime CDN fallback.

Translation issues are warnings in `report` mode and do not invalidate a
successful build. Report them separately from build/static readiness. A changed
reusable example invalidates isolated page rebuilding because every consumer
must move to the same receipt.

## Static verification

```bash
php vendor/bin/docara verify-static build_production
```

The verifier reads static bytes and receipts; it does not execute project PHP
or repair output. It checks:

- manifest/page/output equality and absence of extra HTML surface;
- local routes, links, assets, fragments, and safe fragment encoding;
- duplicate HTML IDs;
- search index/runtime and indexed-page contract;
- redirect receipts, pages, canonicals, and targets;
- component catalogue and Markdown ownership;
- exact Framework and Smart asset projection;
- missing, duplicate, symlinked, or hardlinked output.

Never fix a verifier error inside `build_production`. Correct the owning
Markdown, JSON, asset, lock, registry, or engine source and rebuild.

## HTTP and preview

Serve the exact verified bytes:

```bash
php vendor/bin/docara serve production \
  --host=127.0.0.1 --port=8000 --no-build
```

Do not use `file://`; routes, query-revisioned assets, search, and fragments
must be checked through HTTP.

Test representative root and nested routes, navigation, search, assets,
fragments, light/dark/system themes, responsive regions, focus, keyboard, and
browser console/network behavior according to impact.

The SDK `preview` command is an isolated production-path artifact preview. It
does not create an accepted full-build receipt and does not replace
`verify-static` or site acceptance.

## Determinism

For release evidence, build from the same immutable source and dependency tuple
in independent clean destinations and compare canonical tree digests. A green
mutable-worktree build or a reused ignored output is not reproducibility
evidence.

Keep these states separate:

```text
build succeeded
!= static output verified
!= browser/QA accepted
!= release package ready
!= published
!= deployed and smoke-tested
```
