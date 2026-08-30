# Architecture and project model

## Contents

- [Mental model](#mental-model)
- [Compiler pipeline](#compiler-pipeline)
- [Project files](#project-files)
- [Ownership](#ownership)
- [Initialize a project](#initialize-a-project)
- [Source discovery](#source-discovery)

## Mental model

Docara is a standalone PHP static-site compiler. It consumes Markdown,
validated JSON, admitted design definitions, exact-pinned Smart artifacts, and
project assets. It emits static HTML, CSS, JavaScript, assets, search data,
redirects, and machine-readable receipts. The published site has no database,
backend, daemon, or CDN dependency.

Normal authoring and build require PHP 8.2+, Composer, and `simai/docara`.
Node.js is not part of the portable project path. Optional browser/visual QA or
package-maintainer asset work may use additional tooling.

## Compiler pipeline

Use this architecture when diagnosing ownership or impact:

```text
Markdown source
-> typed in-memory Document IR
-> Document Renderer Registry
-> Smart Component Gateway when a Smart leaf is present
-> Layout / Region / Section / Block composition
-> publisher shell
-> HTML + assets + derived views + receipts
-> atomic build directory promotion
```

The same `PageBuilder` compiles a route in full and single-page builds. The
build modes differ by selected route set and accepted global context, not by a
second renderer.

Cross-page surfaces are derived from the same physical Markdown route set:

- canonical topology and visible navigation;
- breadcrumbs and previous/next links;
- outlines and heading fragments;
- backlinks and component indexes;
- locale alternates;
- search index;
- redirects and public route receipts.

Do not maintain a second public page catalogue or hand-edit derived output.

## Project files

```text
composer.json
composer.lock
vendor/
docara.json
redirects.json                         # optional when no explicit redirects exist
simai-framework.lock.json
assets/
examples/                              # optional shared HTML/CSS/JS demonstrations
translations.lock.json                 # optional accepted translation state
content/
  en/
    lang.json
    section.json                       # optional
    index.md
    guides.md
    guides/
      section.json                     # optional
      install.md
      install.page.json                # optional
smart/                                 # optional project-owned Smart artifacts
design/                                # optional project-owned design artifacts
.docara/
  engine/                              # package-owned state
build_local/                           # generated
build_production/                      # generated
```

Use the flat section form by default: `guides.md` owns `/guides/`, while the
sibling `guides/` directory contains descendants. `guides/index.md` is also
supported, but both forms for one route are an error.

## Ownership

Project-owned authoring surfaces:

- `docara.json`, `redirects.json`, and the consumer-owned `composer.lock`;
- `content/**`, including Markdown, `section.json`, page sidecars, `lang.json`,
  and colocated content assets;
- root `assets/**`;
- reusable `examples/**` and a configured `translations.lock.json`;
- admitted project `smart/**` and `design/**` sources;
- the exact Framework lock selected by the project.

Package-owned or generated surfaces:

- `.docara/engine/**` and its ownership manifest;
- update plans and rollback packages;
- `build_*` and build-local `.docara/**` receipts;
- generated `_docara/**` assets, search data, and catalogues.
- generated `.docara/examples.json` and `.docara/translation-status.json`.

Use product commands to update package-owned state. Never fix source behavior
by editing generated output.

## Initialize a project

Create the exact Composer runtime in the project itself, then run `init` in the
same directory:

```bash
mkdir /path/to/project && cd /path/to/project
composer require simai/docara:^2.0
php vendor/bin/docara init .
php vendor/bin/docara doctor --json
php vendor/bin/docara build production
php vendor/bin/docara verify-static build_production
```

`init` accepts an empty directory or one containing only that verified
project-local Composer runtime. `init --update` is disabled. Updating an
existing project uses the transactional `upgrade` workflow described in
[update-release-and-publication.md](./update-release-and-publication.md).

Do not treat `init` as an importer for a legacy static-site generator, an older
Docara project model, or an arbitrary documentation tree. Inventory and map
legacy sources first, then create a new portable project or an explicit
migration plan.

## Source discovery

Before a change:

1. Locate `docara.json` and record the project root.
2. Record Git status and the exact installed package/source revision.
3. Read declared locale roots and the Framework lock path.
4. Resolve the requested public route to its one physical Markdown owner.
5. Read every inherited `section.json` from locale root to the page and the
   optional page sidecar.
6. Use `docara inspect`, `schema`, or build receipts for exact effective
   contracts.

If local dependencies are unavailable, report the missing runtime instead of
silently switching to another checkout or stale global Docara installation.
