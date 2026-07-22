#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SKILL="$ROOT/skills/docara"

grep -q '^name: docara$' "$SKILL/SKILL.md"
grep -q 'docara init' "$SKILL/SKILL.md"
grep -q 'docara verify-static' "$SKILL/SKILL.md"
grep -q 'content/<locale>' "$SKILL/SKILL.md"
grep -q 'section.json' "$SKILL/references/project-model.md"
grep -q 'build_production' "$SKILL/references/github-pages.md"

if grep -R -n -E 'Jigsaw|Laravel Mix|source/_core|\.settings\.php|init --portable' "$SKILL"; then
  echo 'FAIL: legacy Docara contract remains in skill sources' >&2
  exit 1
fi

echo 'smoke ok'
