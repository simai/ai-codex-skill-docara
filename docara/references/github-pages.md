# GitHub Pages for Docara

Use this when publishing a Docara site publicly.

## Default Recommendation

Prefer GitHub Actions artifact publishing:

- Source docs stay in `source/docs` for root-level Docara, or `docara/source/docs` for a contained subproject.
- Build output stays uncommitted in `build_production` or `docara/build_production`.
- GitHub Pages deploys the generated static site.
- No `gh-pages` subtree or committed root `/docs` artifacts are required.

Generate the workflow:

```bash
python3 <skill>/scripts/create-github-pages-workflow.py --root=. --workflow=.github/workflows/docara-pages.yml
```

For a contained `docara/` subproject:

```bash
python3 <skill>/scripts/create-github-pages-workflow.py --root=. --docara-dir=docara --workflow=.github/workflows/docara-pages.yml
```

The repository owner still needs to set GitHub repository Settings -> Pages -> Source -> GitHub Actions.

If the build job succeeds but the deploy job fails, check whether Pages is enabled before changing the workflow:

```bash
curl -s https://api.github.com/repos/<owner>/<repo>/pages
```

`404` usually means GitHub Pages is not enabled for the repository, or the repository is not configured to build from GitHub Actions. In that case the workflow can upload a valid artifact but `actions/deploy-pages` cannot publish it until an admin enables Settings -> Pages -> Source -> GitHub Actions. After changing the setting, rerun the failed workflow or push a no-op commit.

If the repository uses Yarn and no root `yarn.lock` exists yet, pass the package manager explicitly:

```bash
python3 <skill>/scripts/create-github-pages-workflow.py --root=. --package-manager=yarn
```

## Required Workflow Shape

The workflow should:

1. Checkout repository.
2. Set up PHP 8.2 and Composer.
3. Set up Node 20.
4. Install Composer dependencies.
5. Run `php vendor/bin/docara init --update` from the Docara project directory.
6. Install frontend dependencies.
7. Run `yarn prod` or `npm run prod`.
8. Run `php vendor/bin/docara build production` from the Docara project directory.
9. For GitHub project pages (`https://owner.github.io/repo/`), rewrite generated root-relative links from `/...` to `/repo/...`.
10. Upload the matching build output directory.
11. Deploy with `actions/deploy-pages`.

If the repository was bootstrapped with npm and `webpack` was pinned locally after a real build test, commit `package-lock.json` so CI uses the same dependency graph.

## Project Pages Path Prefix

Docara can generate root-relative paths such as `/assets/build/...`, `/en`, and `/ru`. These are fine on a dedicated domain or an organization/user Pages repository, but they break project Pages where the site lives under `/<repo>/`.

For GitHub project Pages, use the generated workflow's `Adjust GitHub project Pages paths` step. It detects `GITHUB_REPOSITORY` and prefixes HTML/JSON links with `/<repo>`. For custom domains or organization/user repositories such as `simai.github.io`, set:

```yaml
DOCARA_PAGES_PREFIX: "none"
```

If a Docara project uses `baseUrl`, make it environment-driven so CI can set the final URL:

```php
'baseUrl' => rtrim((string) (getenv('DOCARA_BASE_URL') ?: ''), '/'),
```

The generated workflow sets:

```yaml
DOCARA_BASE_URL: "https://${{ github.repository_owner }}.github.io/${{ github.event.repository.name }}"
```

After publishing, verify both pages and assets:

```bash
curl -I https://<owner>.github.io/<repo>/en/
curl -I https://<owner>.github.io/<repo>/assets/build/css/main.css
```

The HTML must not contain unprefixed project-page links:

```bash
curl -s https://<owner>.github.io/<repo>/en/ | grep 'href="/assets'
```

Also check common section URLs and assets after deploy. A successful `index.html` response is not enough if menu links or breadcrumbs still point to missing paths:

```bash
curl -I https://<owner>.github.io/<repo>/<locale>/<section>/
curl -I https://<owner>.github.io/<repo>/assets/build/css/main.css
curl -s https://<owner>.github.io/<repo>/<locale>/ | grep '/<locale>/<locale>'
```

## Root `/docs` Publishing

GitHub Pages can publish from root `/docs` on a branch, but Docara's `source/docs` is source content, not built HTML.

Only use root `/docs` publishing if the project intentionally commits build output:

```php
// config.production.php
<?php
return [
    'production' => true,
    'build' => [
        'destination' => 'docs',
    ],
];
```

Then run:

```bash
yarn prod || npm run prod
php vendor/bin/docara build production
touch docs/.nojekyll
```

This is simpler for GitHub settings but creates build artifact churn in normal commits.

## Separate Repository

Use a separate docs repository only when there is a real governance reason:

- Public docs for a private codebase.
- Different maintainers or approvals.
- Dedicated documentation product with independent releases.
- Different domain, analytics, or compliance controls.

Otherwise keep docs in the product repository and publish with Actions.

## Base URL

For GitHub project pages, set `baseUrl` to the final Pages URL, usually:

```php
'baseUrl' => 'https://<owner>.github.io/<repo>',
```

For an organization/user Pages repository named `<owner>.github.io`, use:

```php
'baseUrl' => 'https://<owner>.github.io',
```

Prefer putting production-only URL values in `config.production.php`.
