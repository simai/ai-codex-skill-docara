# Content, locales, and navigation

## Contents

- [Markdown ownership](#markdown-ownership)
- [Authoring rules](#authoring-rules)
- [Routes and assets](#routes-and-assets)
- [Locales](#locales)
- [Translation tracking](#translation-tracking)
- [Documentation source tracking](#documentation-source-tracking)
- [Navigation and reading context](#navigation-and-reading-context)
- [Redirects](#redirects)
- [Content workflow](#content-workflow)

## Markdown ownership

Each public route has one physical Markdown owner under its declared locale
root. Prefer ordinary Markdown for headings, paragraphs, links, images, lists,
quotes, code, tables, and footnotes. Introduce a component only when the
fragment has separate semantics, parameters, state, or composition rules.

Start a page with one H1. Use supported front matter before the H1 when page
metadata is needed:

```markdown
---
title: Installation
description: Build the first Docara site.
tags: [start, install]
draft: false
translation_key: guide.install
profile: how_to
---

# Installation
```

Do not add arbitrary front matter, layout instructions, executable code, or raw
HTML. Raw HTML is rejected. Use an admitted component for rich content.

## Optional page authoring profiles

An optional root `docara.authoring.json` may declare audiences, one default
profile, and path-to-profile rules. It must not store pages, versions, hashes,
translation state, catalog copies, or editorial prose. Profile precedence is:
page front matter, matching path rule, then default. Conflicting matching rules
are an error.

The built-in profiles are `landing`, `article`, `tutorial`, `how_to`,
`reference`, and `explanation`. They provide measurable structural signals and
an editorial checklist. Docara reports deterministic facts; clarity,
usefulness, audience fit, rationale and semantic completeness remain
`review_required` for a person or external agent. Absence of the authoring file
preserves legacy behavior.

## Authoring rules

- Keep one clear page purpose and a navigable heading hierarchy.
- Preserve stable route paths and heading fragments unless the change includes
  redirects and link repair.
- Keep code fences, inline code, component IDs, props, JSON, URLs, and schema
  keys exact during editorial or translation work.
- Use root/public routes according to the project's `base_url` contract.
- Keep local images beside Markdown or under project `assets/`; validate their
  published paths.
- Never place confidential material in Markdown. Hidden navigation and
  `search.indexed: false` do not make public HTML private.

## Routes and assets

The content path, optional page slug, locale prefix, and `base_url` determine
the public URL and output path. Adding, deleting, or renaming a route requires
a complete build.

Heading IDs are deterministic Unicode fragments. Changing a heading can break
deep links, so always rerun `verify-static` after heading edits.

The build copies only admitted, confined content and brand assets. Do not use
absolute paths, parent traversal, symlinks, hardlinks, reserved build paths, or
unsafe protocols.

## Locales

Declare every locale in `docara.json` with:

- label and direction (`ltr` or `rtl`);
- content root;
- public prefix;
- explicit UI-copy fallbacks when supported.

Each locale owns its own Markdown tree. `missing_page_policy` explicitly
chooses whether missing translations are skipped or treated as errors. Docara
does not silently copy editorial prose from another locale.

Put public shell/interface strings in:

```text
content/<locale>/lang.json
```

Keep article prose in Markdown and component contract data in manifests. Do not
move public prose into package config or design definitions.

Use a stable `translation_key` to connect corresponding routes across locales.
Verify alternate links, locale menus, directionality, UI copy, and search for
every changed locale.

## Translation tracking

When `docara.json` enables `translation_tracking`, treat its report as the
current work queue for substantive content or localization work:

```bash
php vendor/bin/docara translations status
php vendor/bin/docara translations status --locale=en
php vendor/bin/docara translations status --status=stale --json
```

The configured source locale is authoritative. Equal locale-relative paths
pair automatically; different paths require the same explicit
`translation_key` on both pages. Never infer a relationship from titles or
similar prose. A fallback used for UI copy is reported separately and does not
count as a completed translation.

Preserve code fences and their contents, URLs, component IDs and attributes,
table/list shape, and heading levels while translating. Resolve
`duplicate_key` and `structure_mismatch` before acceptance. Leave ambiguous
`missing`/`orphan` pages visible as blockers or add a reviewed exclusion with a
specific reason.

After checking the translation, create and apply an exact plan:

```bash
php vendor/bin/docara translations accept \
  --locale=en --key=guide.install --review=ai_verified --dry-run --json
php vendor/bin/docara translations accept --apply=<plan-sha256> --json
```

Use `--kind=lang` for a `lang.json` key. Promote to `human_reviewed` only after
an explicit editorial review. Docara never calls AI or the network and never
edits Markdown; `status` and `build` are read-only with respect to source and
the lock. Any changed input invalidates the plan.

## Documentation source tracking

`documentation_tracking` is a separate optional contour for the base locale.
It compares Markdown and reusable Example IDs with a project-owned public API
contract. It does not translate pages, duplicate a component registry or infer
relations from similar prose.

Read `documentation status` before creating or updating reference material.
Treat `new`, `changed`, `missing_example`, `unverified`, and `orphan` as an
explicit work queue. One page may document several entities, but every required
example case must map to a concrete reusable Example ID. Leave ambiguous
relations visible; exclusions require a specific reviewed reason.

## Navigation and reading context

Docara derives canonical topology from physical Markdown routes and metadata.
`navigation.hidden` affects the visible menu but preserves canonical ancestry
for visible descendants. `navigation.order` controls sibling order.

The same topology drives:

- visible menu and active state;
- breadcrumbs;
- previous/next links;
- search navigation paths;
- locale route context.

TOC is derived from rendered safe Markdown headings and controlled by
`reading.toc`, `reading.toc_depth`, and mobile TOC settings.

Any topology, order, visibility, header-navigation, or shared reading change
requires a complete build.

## Redirects

Use `redirects.json` for project-owned internal route changes. Builder-owned
locale root and legacy-unprefixed redirects come from locale routing policy.

Redirect sources and targets must remain inside the configured site. Reject
external targets, self redirects, chains, cycles, collisions, queries,
fragments, and missing target pages. Verify the generated redirect receipt and
HTML rather than editing redirect output.

## Content workflow

1. Map the requested public URL to its physical Markdown owner.
2. Read inherited settings and related locale routes.
3. Read translation and documentation-source reports when their independent
   tracking contours are enabled.
4. Inspect exact source entities before editing API reference pages/examples.
5. Edit prose and the narrowest required metadata/config owner.
6. Validate local links, component syntax, assets, examples, source relations,
   and translation structure.
7. Choose single-page or complete build by impact.
8. Run `verify-static`.
9. For visible or multilingual changes, serve the verified output over HTTP
   and inspect representative desktop/mobile, theme, locale, navigation,
   search, and fragment scenarios.

Use `$docs` for documentation method and information architecture, `$content`
for substantial editorial/localization work, `$seo` for public search
contracts, and `$tester` for acceptance evidence.
