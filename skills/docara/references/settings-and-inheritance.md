# Settings and inheritance

## Contents

- [Resolution order](#resolution-order)
- [Merge semantics](#merge-semantics)
- [Setting scopes](#setting-scopes)
- [Common setting families](#common-setting-families)
- [Provenance](#provenance)
- [Build impact](#build-impact)

## Resolution order

For every page, resolve settings in this order:

```text
built-in defaults
-> docara.json
-> section.json files from locale root to the page directory
-> <page>.page.json
-> supported front matter metadata + Markdown content
```

General presentation configuration comes from JSON layers. Front matter owns
only supported page metadata; it is not an arbitrary configuration layer.

Every JSON layer must declare its schema:

- `docara.site.v1`;
- `docara.section.v1`;
- `docara.page.v1`.

Validate exact allowed fields against the installed package schemas. Do not
infer a field from a similarly named setting at another scope.

## Merge semantics

| Input | Effective result |
| --- | --- |
| Missing field | Preserve the inherited value. |
| Partial object | Recursively merge declared keys. |
| Array | Replace the inherited array completely. |
| Scalar | Replace the inherited value. |
| Empty presentation `{}` or `[]` | Schema error. |
| `{"$reset": true}` | Clear the inherited branch. |
| Reset plus sibling keys | Clear, then apply the new keys. |

Layout structure is protective complexity. After a layout reset, Docara
restores the registered layout key and complete region map so the page retains
a valid, inspectable frame. Author overrides are still removed.

Remove a nearby override to inherit a parent/default again. Do not copy the
entire effective object into a child file; that obscures provenance and blocks
future default changes.

## Setting scopes

### Site: `docara.json`

Use for project-wide identity and topology:

- site title and preset;
- content roots and locale registry/routing;
- `base_url`, documentation version, and redirects file;
- Framework lock and project Smart namespace;
- global branding, layout, appearance, search, reading, and navigation defaults;
- reader-preference registry.

### Section: nearest `section.json`

Use for one branch and its descendants:

- branch title or preset;
- branding and layout overrides;
- regions and composition;
- navigation visibility/order defaults;
- search and reading behavior.

Do not place content roots, locale registry, `base_url`, documentation version,
redirects file, Framework lock, or project namespace at section scope.

### Page: `<page>.page.json`

Use for one route:

- title, description, slug, and locale where supported;
- page-level presentation, layout, region, navigation, search, and reading
  overrides.

Keep article prose in the Markdown owner. Do not duplicate content in JSON.

### Front matter

Use only `title`, `description`, `tags`, `draft`, and `translation_key` in the
current contract. `draft: true` prevents public publication and cannot be
bypassed by a single-page build.

### Reader preferences

`reader_preferences` defines an allowlisted browser panel. Visitor choices are
stored locally in the browser; they do not modify project source, effective
configuration provenance, or build receipts.

## Common setting families

- `branding`: title, label, logo variants, favicon, mode, and size;
- `layout`: admitted layout key, container width, content gap, scrollbar, and
  registered region composition;
- `settings`: allowlisted theme, modal blur, and UI radius values;
- `navigation`: hidden/order and validated header navigation;
- `search`: search UI and per-page indexing;
- `reading`: breadcrumbs, TOC, mobile TOC, depth, and previous/next;
- `reader_preferences`: site-only registered preference groups/fields;
- `locales` and `locale_routing`: site-only locale trees and public prefixes.
- `translation_tracking`: site-only source locale, report mode, and root lock
  filename; it does not change locale routing, fallback, or missing-page policy.

Configuration selects admitted IDs and data. It never selects a PHP class,
callback, arbitrary template, renderer, or filesystem path.

## Provenance

After a build, inspect:

```text
build_<environment>/.docara/resolved-page-plans.json
```

For a route, use:

- `resolved_page_plan.configuration` for effective settings;
- `provenance` for the owner of each JSON Pointer;
- `trace` for input files, schemas, and hashes;
- `canonical_hash` for the complete plan identity;
- `declarative.plan` for layout, regions, sections, slots, blocks, and
  diagnostics.

Use `docara inspect page /public/route/ --json` when supported by the exact
package. Never edit the generated resolved plan.

## Build impact

Use a complete build for global or cross-page changes: site config, locale
copy, section inheritance, navigation order/visibility, header navigation,
search/reading policy, regions affecting descendants, redirects, locks, and
registries.

Use a single-page build only for an existing route with accepted global
receipts when the edit cannot change shared topology or derived views.
