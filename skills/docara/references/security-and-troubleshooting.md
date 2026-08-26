# Security and troubleshooting

## Contents

- [Fail-closed model](#fail-closed-model)
- [Diagnostic workflow](#diagnostic-workflow)
- [Common failure classes](#common-failure-classes)
- [Static-output failures](#static-output-failures)
- [Runtime discovery](#runtime-discovery)
- [Evidence to retain](#evidence-to-retain)

## Fail-closed model

Docara accepts validated IDs and data, not project-supplied executable hooks.
Preserve these boundaries:

- no parent traversal, absolute/output-root escape, or includes outside owned
  roots;
- no symlink, hardlink, junction-like, or case-collision escape;
- no PHP class, callback, renderer, executable hook, or arbitrary template/
  filesystem path from Markdown or config;
- no raw page HTML/CSS or external embed outside an explicit sandbox/allowlist;
- no namespace collision, implicit provider shadowing, or moving lock revision;
- no scaffold, preview, test, or QA writes into engine, lock, generated, or
  external roots;
- no stale or hash-mismatched apply plan;
- no publication of secrets or private material into static output.

Do not weaken validation to turn a red build green. Identify and correct the
authoritative owner.

## Diagnostic workflow

1. Record the exact command, exit code, package/source revision, project root,
   and Git status.
2. Read the first stable diagnostic code and its safe relative path, JSON
   Pointer, line, and column.
3. Identify the owner: content, site/section/page config, locale copy,
   redirects, asset, Smart/design artifact, Framework lock, engine, or output.
4. Inspect the exact schema, registry, manifest, provider, or receipt.
5. Correct source; do not patch generated output.
6. Repeat the smallest focused validation.
7. Run the build path required by impact and `verify-static`.
8. Serve the verified bytes over HTTP when the failure affects visible/runtime
   behavior.

Prefer `--json` for automation and evidence. Human and JSON diagnostics should
project the same operation result.

## Common failure classes

### Project/configuration

- missing or malformed `docara.json`;
- unknown field, wrong type, invalid enum, or forbidden empty presentation
  branch;
- page outside declared locale root or locale mismatch;
- ambiguous route ownership;
- invalid `base_url`, slug, redirect, brand asset, or output collision;
- unsupported legacy `_section.json` name.

Read effective provenance before changing a parent or child override.

### Document/components

- unknown alias or renderer;
- invalid front matter;
- raw HTML;
- unclosed directive fence;
- invalid prop/view/preset/slot;
- forbidden nesting, child count, order, or depth;
- component required by prose but absent from the admitted registry.

Use `list`, `inspect`, `schema`, and the source location. Do not copy syntax
from an old site blindly.

### Framework/Smart/design

- moving or mismatched revision;
- manifest/provider/consumer metadata mismatch;
- incomplete asset dependency closure;
- unknown project namespace or duplicate ID;
- unsafe template/asset path;
- selected layout/section/block incompatibility;
- isolated test/QA page does not actually resolve the target artifact.

Treat a capability requirement or catalogue entry as non-executable until the
real artifact passes admission.

### Incremental build

A single-page build must stop when the accepted full build is absent, route is
new/missing, purpose differs, engine/dependencies changed, or global receipts
no longer match. Run a complete build; do not bypass the receipt check.

### Shared examples and translation state

- An ID-based example requires `examples/<id>/index.html` and permits only its
  optional CSS, JavaScript, and confined assets.
- A changed shared-example hash requires a complete build.
- Translation acceptance cannot proceed for an orphan, duplicate key, or
  structural mismatch and cannot apply after any bound input changes.
- Never hand-edit generated example or translation reports to hide a source
  problem.

## Static-output failures

Typical verifier markers include:

- `@missing-fragment`;
- `@duplicate-html-id`;
- `@unsafe-fragment-encoding`.

Also inspect missing/extra HTML, broken assets/routes, search revision,
redirect receipts, component catalogue ownership, and Framework projection.
The `<base>` element is forbidden because it would make verifier and browser
resolution diverge.

Fix the source Markdown/JSON/asset/lock/registry and rebuild. Generated
receipts are evidence, not repair inputs.

## Runtime discovery

When `php`, Composer, autoload, or `vendor/bin/docara` is unavailable:

1. inspect the project and package lock before installing anything;
2. discover approved local runtimes and exact executable paths;
3. distinguish missing dependencies from a product defect;
4. avoid a stale global binary or neighboring checkout;
5. record what could not be executed and the exact prerequisite.

Do not run dependency updates, engine updates, or package substitutions merely
to make a diagnostic command available without scope and ownership review.

## Evidence to retain

- exact Git/package revision and dependency tuple;
- target project and route/artifact ID;
- command, exit code, and stable diagnostic code;
- source path/pointer/location and owning layer;
- focused check plus full/single build decision;
- output path and `verify-static` result;
- relevant receipt/hash;
- HTTP/browser/QA evidence when applicable;
- remaining release/deploy/owner boundary.
