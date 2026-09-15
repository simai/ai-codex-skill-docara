# Upgrade, update, release, and publication

## Contents

- [Capability handshake](#capability-handshake)
- [Project-local upgrade](#project-local-upgrade)
- [Rollback](#rollback)
- [Legacy transition and low-level update](#legacy-transition-and-low-level-update)
- [Release and skill gate](#release-and-skill-gate)
- [Static publication](#static-publication)
- [State boundaries](#state-boundaries)

## Capability handshake

Find the exact project-local executable first:

```bash
php vendor/bin/docara capabilities --json
```

Use the returned command definitions, schemas, lifecycle flags and
`docara.ai_contract` version. One installed canonical skill may serve several
Docara 2.x projects; never replace this handshake with assumptions about the
newest package. If an old exact package has no `capabilities`, inspect its help,
README and update documentation and use the legacy path below.

## Project-local upgrade

For a project that owns `composer.json`, `composer.lock` and `vendor/`, the
normal compatible update is:

```bash
git status --short
php vendor/bin/docara upgrade
```

The explicit invocation may use Composer/network. It resolves only a stable
patch/minor version inside the current major and project constraint, installs
an independent candidate, runs candidate doctor, project validation, engine
sync, production build and static verification, then re-hashes every input.
Only after all checks pass may it promote dependencies, engine and the verified
build.

For reviewable automation:

```bash
php vendor/bin/docara upgrade --check --json
php vendor/bin/docara upgrade --to=2.5.0 --dry-run --json
php vendor/bin/docara upgrade --apply=<exact-plan-sha256> --json
```

`--to` must be an exact stable `X.Y.Z`. Reject a branch, moving reference,
prerelease, downgrade, constraint violation or different major. Changed
Composer files, content, examples, assets, settings, translations, Framework
lock, Smart/design source, engine or verified build make the plan stale.

Build, serve, discovery and authoring remain offline. Do not add a background
updater or call upgrade implicitly from those commands.

## Rollback

Both dependency states must exist locally before promotion. Restore with:

```bash
php vendor/bin/docara upgrade --rollback=latest
# or
php vendor/bin/docara upgrade --rollback=<exact-id>
```

Rollback must not need network. It validates the applied lock, vendor, engine
and build hashes before restoring the previous transaction. If a failure occurs
during apply, compensation restores those surfaces automatically and preserves
the last verified build.

## Pre-manifest transition and low-level update

An existing site without the package-owned `.docara/engine` manifest gets one
explicit project-local Composer runtime and one reviewed engine adoption. Do
not delete or move any older engine automatically:

```bash
cd /path/to/site
composer require simai/docara:^2.0
php vendor/bin/docara capabilities --json
php vendor/bin/docara update --dry-run --adopt --json
php vendor/bin/docara update --apply --json
php vendor/bin/docara update --verify --json
php vendor/bin/docara upgrade --check --json
```

Review the adoption plan before apply. It may create only package-owned
`.docara/engine`; it must not change Markdown, configuration, examples or
assets. If `upgrade` reports `UPGRADE_ENGINE_ADOPTION_REQUIRED`, follow this
same route and retry upgrade. Do not replace the explicit adoption with
`init`, a manual directory copy or an implicit write.

For an already selected exact package, retain the low-level engine-only flow:

```bash
php vendor/bin/docara update --verify --json
php vendor/bin/docara update --dry-run --json
php vendor/bin/docara update --apply --json
php vendor/bin/docara update --rollback=latest
```

Its plan may target only `.docara/engine`. It does not run Composer or change
the dependency lock. Unknown files, dirty ownership, conflicts, symlinks or a
stale plan fail before mutation. Never use `init --update`.

## Release and skill gate

Compare the new public AI contract with the previous release. Internal changes
may reuse the current skill. A changed command, safety sequence, ownership rule
or capability requires:

1. an incremented compatible `docara.ai_contract` identity;
2. synchronized canonical raw skill and skill graph;
3. `skill-change-detect`, `skill-graph-sync`, Federation verify and route check;
4. an exact canonical skill commit pinned by the Federation stable release
   lock.

Only then may the Docara package publication gate pass. Federation, not
`docara upgrade`, physically installs that exact skill revision during its own
atomic update. A project command never writes `~/.codex`.

Package preparation remains an exact clean-revision maintainer operation. The
candidate records source revision, planned version/tag, archive hash, file
ledger, dependency inventory and `published=false`. Build it independently in
two clean checkouts and compare byte-exact artifacts. Publication, tag, GitHub
Release and deploy require their own authorization.

## Static publication

Build with the correct `base_url`, run `verify-static`, copy only those bytes
to versioned staging, compare digests, smoke routes/assets/search/themes, then
switch traffic atomically. Use `$ops` for hosting, access, backup, rollback and
live changes. Build success is not deployment authorization.

## State boundaries

Report separately:

- package capability contract read;
- compatible upgrade checked/planned/applied/compensated/rolled back;
- low-level engine update planned/applied/rolled back;
- site build succeeded and static output verified;
- browser/QA accepted;
- release package reproducible and skill gate synchronized;
- release authorized/published;
- site staged/deployed/smoke-tested.

Never promote one state into another without its own evidence.
