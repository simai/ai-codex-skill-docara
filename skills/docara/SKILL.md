---
name: docara
description: Build, author, configure, extend, inspect, preview, test, verify, update, package, and publish standalone Docara 2 documentation and landing sites. Use for Docara projects with docara.json, optional authoring profiles, Markdown content, reusable examples, translation tracking, inherited section/page settings, locales, navigation, search, reader preferences, layouts, regions, typed components, SIMAI Framework or project-owned Smart artifacts, the Developer/AI SDK, static builds, verification, troubleshooting, transactional engine updates, release readiness, or static-host deployment.
---

# Docara

Own Docara-specific project mechanics. Treat Docara as a PHP compiler that
turns Markdown, validated JSON, admitted design artifacts, and exact-pinned
Smart components into a self-contained static site.

## Authority and freshness

Use this precedence:

1. The target project's files and exact installed `simai/docara` package.
2. The matching package schemas, CLI help, README, and documentation.
3. This skill as the operational map and safety contract.

When a command, schema field, component, or artifact contract may have changed,
inspect the exact installed package instead of guessing. If product sources and
this skill disagree, follow the product and report skill drift.

Use the repo-local Mirai Graph only to select capabilities, relations, gates,
and references. Raw skill sources remain authoritative for method and
judgement. Graph output never grants write, release, or deployment authority.

## Mirai Graph Project Technology

Use the public `mirai-graph technology verify` and `mirai-graph technology
context --task "..."` operations for verification and task-scoped context.
The exact Mirai Graph release is supplied by the Federation release lock; raw
Docara sources remain authoritative and unavailable context fails closed.

## Start every task

1. Locate the project root containing `docara.json`.
2. Inspect Git status and preserve unrelated or untracked work.
3. Read `docara.json`, declared locale roots, the nearest relevant
   `section.json`, an optional page sidecar, and the Framework lock reference.
4. Discover the executable from the project (`vendor/bin/docara`) or an exact
   package checkout; do not assume a global binary.
5. Load only the reference that matches the task.

## Task router

| User outcome | Load |
| --- | --- |
| Create, adopt, or understand a project | [architecture-and-project-model.md](./references/architecture-and-project-model.md) |
| Change site, section, page, branding, layout, reading, search, or locale settings | [settings-and-inheritance.md](./references/settings-and-inheritance.md) |
| Write, restructure, translate, migrate, or track content; change routes or navigation | [content-locales-and-navigation.md](./references/content-locales-and-navigation.md) |
| Use inline or reusable examples; native, typed, container, Framework, Docara, or project Smart components | [components-smart-and-design.md](./references/components-smart-and-design.md) |
| Build one page or the full site, preview, serve, or verify output | [build-preview-and-verification.md](./references/build-preview-and-verification.md) |
| Inspect registries, scaffold artifacts, validate, test, run QA, or use MCP | [developer-sdk-and-qa.md](./references/developer-sdk-and-qa.md) |
| Update the engine, prepare a package/release, or publish to a static host | [update-release-and-publication.md](./references/update-release-and-publication.md) |
| Diagnose errors, ownership, paths, locks, links, assets, or security boundaries | [security-and-troubleshooting.md](./references/security-and-troubleshooting.md) |

Read [rules/skill-mesh-balance.md](./rules/skill-mesh-balance.md) when the task
needs documentation, content, SEO, UX, QA, runtime, or release companions.

## Core execution loop

1. Translate the request into one primary outcome and the smallest complete
   project-owned change.
2. Identify the authoritative input: Markdown, front matter, `docara.json`, a
   `section.json`, a page sidecar, `lang.json`, `redirects.json`, `assets/`,
   reusable `examples/`, `translations.lock.json`, optional
   `docara.authoring.json`, `smart/`, or `design/`.
3. Validate IDs and fields against the exact schema, registry, or `inspect`
   result before editing.
4. Make the change without editing generated or package-owned state.
5. Run the cheapest relevant check, then the required build path.
6. Run `verify-static` on the exact output.
7. For visible behavior, serve the verified bytes over HTTP and test the
   affected routes, navigation, search, themes, responsiveness, and keyboard
   behavior as appropriate.
8. Report changed owners, commands, output path, evidence, and any separate
   publication or acceptance gate.

## Build selection

Use a single-page build only for an existing route after an accepted complete
build and only when the engine, dependency tuple, build purpose, topology, and
global registries remain unchanged:

```bash
php vendor/bin/docara build production --page=/public/route/
```

Use a complete build after adding, deleting, or renaming a route; changing a
reusable example, `docara.json`, locale copy, navigation topology, redirects,
Framework lock, shared registries, section inheritance, or other cross-page
state:

```bash
php vendor/bin/docara build production
php vendor/bin/docara verify-static build_production
```

Open exactly those verified bytes over HTTP:

```bash
php vendor/bin/docara serve production \
  --host=127.0.0.1 --port=8000 --no-build
```

## Non-negotiable boundaries

- Never edit `build_*`, build-local `.docara`, or package-owned
  `.docara/engine` as source.
- Never use `init --update`; it is a disabled compatibility guard. Use the
  transactional `update --verify -> --dry-run -> --apply` workflow.
- Never initialize a non-empty target or use `init` as a legacy converter.
- Never replace an exact Framework lock with a branch, moving URL, or `latest`.
- Never admit a component from catalogue prose alone. Require its real
  manifest/provider/lock/registry contract.
- Never pass PHP classes, callbacks, executable hooks, arbitrary template
  paths, raw project CSS, or unsafe filesystem paths through content/config.
- Never weaken traversal, symlink, hardlink, ownership, namespace, schema,
  hash, slot, prop, or static-output checks to make a build pass.
- Keep secrets and private content out of source and static output.
- Treat `build`, `verify-static`, browser acceptance, release readiness,
  package publication, and deployment as separate states.
- Require `$ops`, backup, rollback, and an explicit live authorization before
  deployment or destructive runtime work.

## Output contract

Return:

- project root and exact package/source revision when relevant;
- source owners changed and the effective scope (`site`, `section`, `page`,
  content, Smart, design, engine, or publication);
- checks and commands actually executed;
- build output and static-verification result;
- browser/QA coverage or a precise reason it was not required;
- unresolved diagnostics, owner review, release, or deployment boundary;
- the smallest safe next step.

Do not call a result released, published, or production-ready unless that
specific state has fresh evidence and authorization.
