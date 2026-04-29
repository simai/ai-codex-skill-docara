#!/usr/bin/env python3
"""Create a GitHub Pages workflow for a Docara site."""

from __future__ import annotations

import argparse
from pathlib import Path


WORKFLOW = """name: Publish Docara Site

on:
  push:
    branches: ["main"]
  workflow_dispatch:

permissions:
  contents: read
  pages: write
  id-token: write

env:
  DOCARA_BASE_URL: "https://${{{{ github.repository_owner }}}}.github.io/${{{{ github.event.repository.name }}}}"

concurrency:
  group: "pages"
  cancel-in-progress: false

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "{php_version}"
          coverage: none
          tools: composer

      - name: Set up Node.js
        uses: actions/setup-node@v4
        with:
          node-version: "{node_version}"

      - name: Install Composer dependencies
        working-directory: {docara_dir}
        run: composer install --no-interaction --prefer-dist --no-progress

      - name: Initialize Docara
        working-directory: {docara_dir}
        env:
          DOCARA_SKIP_FRONTEND_INSTALL: "true"
        run: php vendor/bin/docara init --update

      - name: Install frontend dependencies
        working-directory: {docara_dir}
        run: {install_command}

      - name: Build frontend assets
        working-directory: {docara_dir}
        run: {asset_command}

      - name: Build Docara site
        working-directory: {docara_dir}
        env:
          DOCARA_BASE_URL: ${{{{ env.DOCARA_BASE_URL }}}}
        run: php vendor/bin/docara build production

      - name: Adjust GitHub project Pages paths
        env:
          DOCARA_PAGES_PREFIX: "{pages_prefix}"
        run: |
          python3 - <<'PY'
          import os
          import re
          from pathlib import Path

          raw_prefix = os.environ.get("DOCARA_PAGES_PREFIX", "auto").strip()
          if raw_prefix == "auto":
              repository = os.environ.get("GITHUB_REPOSITORY", "")
              repo_name = repository.split("/", 1)[1] if "/" in repository else ""
              prefix = "" if repo_name.endswith(".github.io") else f"/{{repo_name}}"
          elif raw_prefix in ("", "none", "/"):
              prefix = ""
          else:
              prefix = "/" + raw_prefix.strip("/")

          if not prefix:
              raise SystemExit(0)

          root = Path("{artifact_path}")
          patterns = [
              (re.compile(r'(?P<attr>\\b(?:href|src|action)=["\\'])/(?!/)(?P<path>[^"\\']*)'), rf'\\g<attr>{{prefix}}/\\g<path>'),
              (re.compile(r'(?P<attr>\\bcontent=["\\'])/(?!/)(?P<path>[^"\\']*)'), rf'\\g<attr>{{prefix}}/\\g<path>'),
          ]
          for path in list(root.rglob("*.html")) + list(root.rglob("*.json")):
              text = path.read_text(encoding="utf-8")
              updated = text
              for pattern, replacement in patterns:
                  updated = pattern.sub(replacement, updated)
              updated = updated.replace('window.location.replace("', f'window.location.replace("{{prefix}}/')
              updated = updated.replace('window.location.replace(`', f'window.location.replace(`{{prefix}}/')
              updated = updated.replace('"url": "\\/', f'"url": "\\/{{prefix.strip("/")}}/')
              if updated != text:
                  path.write_text(updated, encoding="utf-8")
          PY

      - name: Disable Jekyll
        run: touch {artifact_path}/.nojekyll

      - name: Upload Pages artifact
        uses: actions/upload-pages-artifact@v3
        with:
          path: {artifact_path}

  deploy:
    environment:
      name: github-pages
      url: ${{{{ steps.deployment.outputs.page_url }}}}
    runs-on: ubuntu-latest
    needs: build
    steps:
      - name: Deploy to GitHub Pages
        id: deployment
        uses: actions/deploy-pages@v4
"""


def detect_package_manager(root: Path) -> str:
    if (root / "yarn.lock").exists():
        return "yarn"
    if (root / "pnpm-lock.yaml").exists():
        return "npm"
    return "npm"


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--root", default=".", help="Repository root")
    parser.add_argument("--workflow", default=".github/workflows/docara-pages.yml", help="Workflow path")
    parser.add_argument("--php-version", default="8.2")
    parser.add_argument("--node-version", default="20")
    parser.add_argument("--package-manager", choices=["npm", "yarn"], default=None)
    parser.add_argument("--docara-dir", default=".", help="Docara project directory relative to repository root")
    parser.add_argument(
        "--pages-prefix",
        default="auto",
        help='GitHub Pages path prefix: "auto", "none", or an explicit prefix such as "/my-repo"',
    )
    args = parser.parse_args()

    root = Path(args.root).resolve()
    docara_dir = args.docara_dir.strip().strip("/") or "."
    docara_root = root if docara_dir == "." else root / docara_dir
    manager = args.package_manager or detect_package_manager(docara_root)
    if manager == "yarn":
        install_command = "yarn install"
        asset_command = "yarn prod"
    else:
        install_command = "npm install"
        asset_command = "npm run prod"

    workflow_path = Path(args.workflow)
    if not workflow_path.is_absolute():
        workflow_path = root / workflow_path
    workflow_path.parent.mkdir(parents=True, exist_ok=True)
    workflow_path.write_text(
        WORKFLOW.format(
            php_version=args.php_version,
            node_version=args.node_version,
            docara_dir=docara_dir,
            artifact_path="build_production" if docara_dir == "." else f"{docara_dir}/build_production",
            install_command=install_command,
            asset_command=asset_command,
            pages_prefix=args.pages_prefix,
        ),
        encoding="utf-8",
    )
    print(f"created {workflow_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
