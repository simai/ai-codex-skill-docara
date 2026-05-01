# Docara Project Model

Use this when initializing, repairing, or auditing a Docara project.

## Runtime

- PHP: `^8.2`.
- Composer package: `simai/docara`.
- Frontend: Node 20 with Yarn or npm, Laravel Mix, `yarn prod` or `npm run prod`.
- CLI: `php vendor/bin/docara init`, `php vendor/bin/docara build production`, `php vendor/bin/docara translate`.

## Files

Expected project root files after initialization:

- `.env` with `DOCS_DIR=docs` and optional Azure translation variables.
- `.env.example` with placeholders only.
- `composer.json` requiring `simai/docara`.
- `config.php`.
- `translate.config.php`.
- `package.json`, `webpack.mix.js`, `bootstrap.php`, `eslint.config.js`.
- `source/_core`.
- `source/<DOCS_DIR>/<locale>`.

Docara reads `DOCS_DIR` from environment and maps it to `source/<DOCS_DIR>`. The usual source path is `source/docs/en`.

## Safe Initialization

For a new or existing repository:

```bash
composer require simai/docara
php vendor/bin/docara init --update
yarn prod || npm run prod
php vendor/bin/docara build production
```

If dependency installation should be delayed or CI handles frontend install:

```bash
DOCARA_SKIP_FRONTEND_INSTALL=true php vendor/bin/docara init --update
```

Do not run destructive archive/delete init modes unless the user explicitly asks. `init --update` preserves existing `config.php` and `source/<DOCS_DIR>` and refreshes generated support files.

In Docara `v1.3.39+`, `init --update` also protects git-tracked `source/_core` files. This is safer for project customizations, but it means upstream UI fixes may be reported as `gitSkipped` and will not appear in the project automatically. After updating `simai/docara`, compare `vendor/simai/docara/stubs/site/source/_core` with the project `source/_core` and manually merge relevant fixes such as settings controls, search markup, top-menu classes, table wrapping, and frontend event timing.

## Starter Preparation

Before initializing a mixed or partially prepared repository, run a dry check:

```bash
php <skill>/scripts/prepare-docara-project.php --root=. --docs-dir=docs --locale=en
```

If the plan is correct, create missing safe files:

```bash
php <skill>/scripts/prepare-docara-project.php --root=. --docs-dir=docs --locale=en --write
```

The script does not install Composer packages or overwrite existing files. It creates safe `.env.example`, minimal locale starter files, and required local-only `.gitignore` entries.

Do not ignore `source/` inside the Docara project itself. `source/docs` is the documentation source of truth and must be tracked unless the whole Docara project is intentionally local-only. Ignore generated `source/assets/build/`, `build_*`, `.cache`, `vendor`, and `node_modules`.

## Import Existing Markdown

For a product repository that already has Markdown files, prefer creating a contained Docara project in `docara/`:

```bash
php <skill>/scripts/import-markdown-docs.php --input=. --output=docara --locale=en --title='Project Documentation' --include=README.md,docs,sitepack-spec --write
```

The importer copies Markdown into `docara/source/docs/<locale>`, adds Docara front matter, converts `README.md` to `index.md`, and creates `.settings.php` files for menu discovery.

Use this layout when the repository already has source/docs/build conventions. In a dedicated documentation-only repository, root-level Docara is acceptable.

If a parent `.gitignore` contains `source`, explicitly unignore the nested Docara source:

```gitignore
!docara/source/
!docara/source/**
```

## Runtime Pitfalls

On macOS, Homebrew PHP can be broken after ICU upgrades. If `php` fails with a missing `libicu*.dylib`, use ServBay PHP for Docara commands:

```bash
/Applications/ServBay/script/alias/php vendor/bin/docara init --update
/Applications/ServBay/script/alias/php vendor/bin/docara build production
```

Docara's frontend build should run on Node 20. Very new Node versions can break the Laravel Mix/webpack stack. If no local Node 20 is installed, use:

```bash
npx -p node@20 -c "node node_modules/laravel-mix/bin/cli.js --production"
```

If npm installs a newer incompatible `webpack` and Mix fails with `Progress Plugin` schema errors, pin the version from Docara's bundled `source/_core/yarn.lock`:

```bash
npm install --save-dev webpack@5.99.8
```

When Composer downloads Docara from GitHub ZIP archives on macOS, `unzip` can warn on Unicode filenames such as `markdown‑aware-translation.md` and then fall back to PHP ZipArchive. Treat this as non-fatal only if Composer finishes successfully and `composer show simai/docara --locked` reports the expected version.

Docara `v1.3.39` can emit PHP deprecation warnings `md5(): Passing null` while writing copied asset files when build cache is enabled. For CI and GitHub Pages builds, prefer `'cache' => false` in project `config.php` or a production override until upstream handles non-string copied file contents in cache hashing.

## Config Priorities

Important `config.php` keys:

- `baseUrl`: canonical site URL.
- `siteName`, `siteDescription`: visible metadata.
- `brand`: header branding. Prefer a project/product name in `brand.title` and either a custom `brand.logoSvg` or a neutral default icon. Do not leave sample logos such as `simai ui`, and do not append generic suffixes like `Docs` or `Documentation` unless they are part of the actual product name.
- `github`: repository URL used by "Edit on GitHub".
- `githubEditBasePath`: project-local extension used by the `$docara` skill for contained subprojects such as `docara/`. When Docara lives under `docara/` inside a product repository, set `'githubEditBasePath' => 'docara'` and wrap `gitHubUrl` so edit links point to `docara/source/docs/...`, not root `source/docs/...`.
- `locales`: map like `['en' => 'English', 'ru' => 'Русский']`.
- `defaultLocale`: base language.
- `category`: single tree vs category navigation mode.
- `layout`: header, sidebars, footer, floating controls.
- `build.destination`: optional custom output path, usually in `config.production.php`.

Use environment-specific `config.production.php` for publication-only changes such as `baseUrl`, `production`, or `build.destination`.

## Doctor Checklist

Run:

```bash
php <skill>/scripts/docara-doctor.php --root=.
```

Then fix in this order:

1. Composer and PHP package.
2. `.env` and `DOCS_DIR`.
3. `config.php` and locales.
4. `source/<DOCS_DIR>/<locale>` tree.
5. Frontend package manager and assets.
6. Build output.
7. Publication workflow.
