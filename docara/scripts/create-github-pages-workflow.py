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
        run: composer install --no-interaction --prefer-dist --no-progress

      - name: Initialize Docara
        env:
          DOCARA_SKIP_FRONTEND_INSTALL: "true"
        run: php vendor/bin/docara init --update

      - name: Install frontend dependencies
        run: {install_command}

      - name: Build frontend assets
        run: {asset_command}

      - name: Build Docara site
        run: php vendor/bin/docara build production

      - name: Disable Jekyll
        run: touch build_production/.nojekyll

      - name: Upload Pages artifact
        uses: actions/upload-pages-artifact@v3
        with:
          path: build_production

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
    args = parser.parse_args()

    root = Path(args.root).resolve()
    manager = args.package_manager or detect_package_manager(root)
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
            install_command=install_command,
            asset_command=asset_command,
        ),
        encoding="utf-8",
    )
    print(f"created {workflow_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
