# Update, release, and publication

## Contents

- [Transactional engine update](#transactional-engine-update)
- [Rollback](#rollback)
- [Release package preparation](#release-package-preparation)
- [Static publication](#static-publication)
- [GitHub Pages](#github-pages)
- [State boundaries](#state-boundaries)

## Transactional engine update

Select an exact new `simai/docara` package version or source revision first.
The consumer owns dependency selection and `composer.lock`; `docara update`
does not run Composer or change that lock.

Use this sequence:

```bash
git status --short
php vendor/bin/docara verify-static build_production
php vendor/bin/docara update --verify --json
php vendor/bin/docara update --dry-run --json
```

Review every planned add/replace/delete. The plan may target only
`.docara/engine` package-owned state. Stop if it includes content, assets,
`docara.json`, redirects, section/page settings, locale files, Framework lock,
or `composer.lock`.

Apply only the unchanged plan:

```bash
php vendor/bin/docara update --apply --json
php vendor/bin/docara build production
php vendor/bin/docara verify-static build_production
php vendor/bin/docara serve production \
  --host=127.0.0.1 --port=8000 --no-build
```

Unknown files, dirty engine ownership, conflicts, symlinks, or stale/hash-
mismatched plans must fail before mutation. Never use `init --update`.

## Rollback

Apply records an immutable rollback package and identifier. Restore with:

```bash
php vendor/bin/docara update --rollback=latest
# or
php vendor/bin/docara update --rollback=<exact-id>
```

Rollback validates its manifest, hashes, and lock before replacement. Rebuild,
run `verify-static`, and repeat HTTP smoke after restoration. A damaged or
unverifiable rollback package is a blocker, not a reason to copy files by hand.

## Release package preparation

Package preparation is a source-repository maintainer operation, not ordinary
site authoring. Use an exact clean revision and the product's current release
scripts. A candidate package must record:

- source revision and planned version/tag;
- deterministic archive hash and full file ledger;
- exact dependency and Framework tuples;
- consumer ownership boundary;
- dependency inventory/SBOM when the product contract requires it;
- `published=false` until a separate release decision.

Build the candidate independently in two clean checkouts and compare byte-
exact package artifacts. Install each into a fresh consumer and run
init/update/build/static/browser/rollback checks required by the release plan.

Do not create a version, tag, GitHub Release, package publication, or deployment
unless the user explicitly authorizes that exact lifecycle action.

## Static publication

1. Set the correct production `base_url` before building.
2. Build `production` and run `verify-static`.
3. Record a deterministic digest of the verified directory.
4. Copy only those bytes to a new versioned staging destination.
5. Recompute and compare the staging digest.
6. Smoke root/nested routes, CSS/assets, search, fragments, themes, and redirect
   boundaries on staging.
7. Atomically switch traffic using the hosting platform's release mechanism.
8. Repeat smoke and retain the previous successful release for rollback.

Use `$ops` for hosting, access, backup, rollback, traffic switching, and live
incident response. Build success alone never authorizes publication.

## GitHub Pages

Prefer Actions artifact deployment:

- install exact PHP/Composer dependencies;
- build production;
- verify `build_production`;
- add `.nojekyll` only as a hosting artifact after the accepted build contract
  permits it;
- upload the verified directory;
- deploy with the official Pages action and required permissions.

Use `/<repository>/` as `base_url` for project Pages unless a custom domain or
owner Pages repository serves the site at `/`. Do not rewrite generated HTML
after verification to repair paths.

Pages configuration, permissions, external Actions, deployment, and rollback
are live state. Require explicit authorization and `$ops` evidence.

## State boundaries

Report these separately:

- engine update planned/applied/rolled back;
- site build succeeded;
- static output verified;
- browser/QA accepted;
- release package reproducible;
- release authorized/published;
- site staged/deployed/smoke-tested;
- rollback proven.

Never promote one state into another without its own evidence.
