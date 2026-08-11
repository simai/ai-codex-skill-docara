# Docara Skill

The canonical English owner skill for building and operating standalone
Docara 2 sites with Codex.

The skill understands Docara as a PHP static compiler: Markdown and validated
project configuration become typed document IR, renderer output, admitted
Smart components, composed layouts, static pages, derived indexes, and
verification receipts. It covers:

- project structure and ownership;
- content, locales, routes, navigation, and redirects;
- inherited site, section, page, reading, search, branding, and layout settings;
- native, typed, container, Framework, Docara, and project Smart components;
- full and single-page builds, preview, static verification, and browser QA;
- Developer/AI SDK discovery, scaffolding, validation, tests, structured QA,
  and MCP integration;
- transactional engine updates, rollback, release readiness, publication, and
  troubleshooting.

The installable entry point is [skills/docara/SKILL.md](skills/docara/SKILL.md).
The compact capability graph is [graph/specs/index.json](graph/specs/index.json).
The Docara product and the target project's exact installed package remain the
source of truth for schemas and executable behavior.

## Validation

```bash
python3 /Users/rim/.codex/skills/.system/skill-creator/scripts/quick_validate.py skills/docara
python3 scripts/validate_skill_contract.py
bash tests/smoke.sh
```

This repository does not install, enable, release, or deploy itself.
