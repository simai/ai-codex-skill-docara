# Docara 2 project model

## Runtime

- PHP `^8.2` and Composer.
- Package `simai/docara`.
- No Node.js, project PHP configuration or frontend build is required.

## Initialize and verify

```bash
composer require simai/docara
php vendor/bin/docara init
php vendor/bin/docara build production
php vendor/bin/docara verify-static build_production
```

The initializer requires an empty target unless `--update` is explicit. Update
is for the current JSON/Markdown project format; it is not a converter.

## Files

```text
docara.json
redirects.json
simai-framework.lock.json
assets/
content/
  <locale>/
    section.json
    index.md
    index.page.json   # optional
```

Resolution order is built-in defaults, `docara.json`, every `section.json`
from the locale root to the page, optional page JSON, then Markdown content.

## Locales and versions

Every locale is declared in `docara.json` and has its own content tree. Keep
locale routing symmetric and user-facing system strings in language packs.
Treat a documentation version as a separate site variant/output with its own
`base_url`; do not silently mix versions in one build.

## Updating

Before `init --update`, record the package revision and working tree, back up
project-owned files, and inspect the resulting diff. Build into a staged output,
run `verify-static`, browser-smoke representative routes, then deploy with a
known rollback.

## Troubleshooting order

1. Validate JSON and the reported JSON Pointer.
2. Check the locale registry and inherited section/page settings.
3. Check the immutable Framework lock and component catalogue.
4. Rebuild and run `verify-static`.
5. Open the exact verified output through `docara serve`, never `file://`.
