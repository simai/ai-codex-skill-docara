#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SKILL="$ROOT/skills/docara"

grep -q '^name: docara$' "$SKILL/SKILL.md"
grep -q 'update --verify' "$SKILL/SKILL.md"
grep -q 'verify-static' "$SKILL/SKILL.md"
grep -q 'Smart' "$SKILL/references/components-smart-and-design.md"
grep -q 'examples/<stable-id>/' "$SKILL/references/components-smart-and-design.md"
grep -q 'translations status' "$SKILL/references/content-locales-and-navigation.md"
grep -q 'scaffold' "$SKILL/references/developer-sdk-and-qa.md"
grep -q 'single-page' "$SKILL/references/build-preview-and-verification.md"

python3 "$ROOT/scripts/validate_skill_contract.py"

if grep -R -n -E 'Jigsaw|Laravel Mix|source/_core|\.settings\.php|init --portable' \
  "$SKILL" "$ROOT/README.md" "$ROOT/CHANGELOG.md"; then
  echo 'FAIL: legacy Docara contract remains in canonical sources' >&2
  exit 1
fi

echo 'docara skill smoke: ok'
