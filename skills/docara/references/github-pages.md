# GitHub Pages for Docara 2

Prefer Actions artifact publishing: source stays in the repository,
`build_production` remains uncommitted, and Pages deploys the verified artifact.

The build job must install PHP/Composer dependencies, run
`docara build production`, run `docara verify-static build_production`, add
`.nojekyll`, and upload `build_production`. The deploy job uses
`actions/deploy-pages`.

Set the project's production `base_url` in `docara.json` for the actual Pages
path. Do not rewrite generated HTML after verification: that invalidates
receipts and determinism. For project Pages, use `/<repository>/`; for a custom
domain or owner Pages repository, use `/` or its canonical public base.

Publishing is live external state. Require repository Pages configuration,
appropriate permissions, successful build/verify evidence and a rollback path
before deployment.
