# Components, Smart artifacts, and design composition

## Contents

- [Selection ladder](#selection-ladder)
- [Typed Document IR](#typed-document-ir)
- [Reusable examples](#reusable-examples)
- [Smart providers](#smart-providers)
- [Project-owned Smart](#project-owned-smart)
- [Design composition](#design-composition)
- [Admission and safety](#admission-and-safety)

## Selection ladder

Choose the smallest supported level:

1. Native Markdown for ordinary document semantics.
2. Inline Docara components for admitted inline behavior such as a badge,
   link-button, icon, or keyboard key.
3. Typed block components for a standalone semantic block.
4. Typed containers for constrained child composition.
5. Exact-admitted SIMAI Framework Smart artifacts.
6. Project-owned Smart or design artifacts when existing capabilities are
   insufficient.

Do not create project CSS, templates, or a new Smart artifact when native or
already admitted behavior solves the outcome.

Inspect the exact current capability before authoring:

```bash
php vendor/bin/docara list smart --json
php vendor/bin/docara inspect smart ui.alert --json
php vendor/bin/docara schema smart --json
```

Catalogue metadata and documentation explain usage but do not grant
admission. The manifest/provider/registry/lock contract is authoritative.

## Typed Document IR

Markdown compiles once into typed in-memory `docara.document_ir.v1` nodes. The
registry distinguishes source Markdown, inline and block components,
containers, and Smart calls. Every node retains source location.

The Document Renderer Registry rejects an unknown node renderer. Containers
validate allowed children, slots, count, order, and relative subtree depth.
The composition layer must preserve byte-semantic parity of the already
rendered main document.

## Reusable examples

Keep a small one-off demonstration inline in `:::example`. When the same
HTML/CSS/JavaScript is reused by several pages or locales, place it under:

```text
examples/<stable-id>/
├── index.html
├── index.css              # optional
├── index.js               # optional
└── assets/                # optional
```

Reference it without an inline body:

```markdown
:::example {id="utilities/animation-duration" label="Result"}
:::
```

Use either `id` or inline fenced sources, never both. Keep shared demonstration
copy in English unless the project has an explicit example-localization
contract. A full build emits only existing source tabs, confines and copies
allowed assets relative to `base_url`, and records hashes, consumers, and
outputs in `.docara/examples.json`. Any shared-example change requires a full
build; do not bypass the single-page receipt guard.

Never use traversal, absolute source paths, symlinks, hardlinks, case-colliding
entries, unknown file types, invalid UTF-8, or oversized sources/assets. The
JavaScript preview remains inside the sandboxed iframe.

## Smart providers

Provider ownership is namespace-based:

- `ui.*`: exact-pinned SIMAI Framework artifacts;
- `docara.*`: package-owned shell and navigation artifacts;
- the namespace declared in `docara.json`: project-owned artifacts.

All providers use the same Smart Gateway and neutral portable contract:

```text
sf.smart_artifact_abi / 1.0.0 / sf-smart-artifact-abi-v1
```

The gateway resolves provider ownership, manifest, view or preset, props,
slots, template, assets, and hydration before rendering. Compatibility adapters
do not create a second public authoring format.

## Project-owned Smart

Declare one safe namespace in `docara.json`, then use the fixed `smart/` root:

```text
smart/project.notice/
├── manifest.json
├── view/default.json
├── template/default.php
└── assets/notice.css
```

Prefer the hash-bound SDK workflow:

```bash
php vendor/bin/docara scaffold smart project.notice --dry-run --json
php vendor/bin/docara scaffold --apply=<exact-plan-sha256> --json
php vendor/bin/docara validate smart project.notice
php vendor/bin/docara inspect smart project.notice --json
```

Apply only the unchanged plan after reviewing every created path and hash.
Scaffold may create new project-owned files under `smart/` or `design/`; it
must not overwrite an existing artifact or write to engine, content, lock,
assets, build, or external roots.

Portable templates receive checked arrays and rendered child HTML. Escape
public values. Do not introduce callbacks, runtime service access, backend
effects, fetch, arbitrary includes, or a Docara-specific template dialect.

## Design composition

The registered composition hierarchy is:

```text
Layout -> Region -> Section -> Slot -> Block -> Smart/View
```

- Layout owns page geometry and region names.
- Regions are insertion points such as `header`, `sidebar`, `main`, `outline`,
  and `footer`.
- Section declares compatible regions and slots.
- Block declares payload schema/capability.
- Smart/View renders validated data to HTML/assets/provenance.

Configuration selects registered IDs; it never names a class, callback,
renderer, PHP file, template path, or arbitrary utility.

Use `docara atlas --json` and `list/inspect` to discover admitted layouts,
views, sections, blocks, slots, capabilities, and ownership. A project design
scaffold can create a coherent layout/view/section/block set without changing
engine `src/`.

## Admission and safety

Fail closed on:

- unknown component, alias, prop, state, view, preset, slot, or child;
- invalid count, order, nesting depth, or unclosed fence;
- namespace collision or package shadowing;
- moving or hash-mismatched Framework revision;
- incomplete transitive asset projection;
- traversal, symlink, hardlink, case collision, unsafe file type, or arbitrary
  template path;
- unsupported backend handlers, data binding, or effects.

Changing an engine-owned typed definition, bundled registry, Framework
admission, or product schema is Docara engine development. Route that work to
`$dev`, preserve exact-lock evidence, add positive/negative tests and one
physical Markdown owner, then run full static and browser acceptance.
