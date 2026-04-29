# GitHub Pages for Docara

Use this when publishing a Docara site publicly.

## Default Recommendation

Prefer GitHub Actions artifact publishing:

- Source docs stay in `source/docs`.
- Build output stays uncommitted in `build_production`.
- GitHub Pages deploys the generated static site.
- No `gh-pages` subtree or committed root `/docs` artifacts are required.

Generate the workflow:

```bash
python3 <skill>/scripts/create-github-pages-workflow.py --root=. --workflow=.github/workflows/docara-pages.yml
```

The repository owner still needs to set GitHub repository Settings -> Pages -> Source -> GitHub Actions.

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
5. Run `php vendor/bin/docara init --update`.
6. Install frontend dependencies.
7. Run `yarn prod` or `npm run prod`.
8. Run `php vendor/bin/docara build production`.
9. Upload `build_production`.
10. Deploy with `actions/deploy-pages`.

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
