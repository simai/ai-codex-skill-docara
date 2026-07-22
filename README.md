# ai-codex-skill-docara

Codex owner skill for Docara 2 documentation sites.

It covers the PHP-only JSON/Markdown project model, inherited configuration,
Simai Framework components, deterministic build and verification, migration
planning and static publication handoff.

The skill lives in `skills/docara/` and is invoked as `$docara`.

## Validate

```bash
python3 /Users/rim/.codex/skills/.system/skill-creator/scripts/quick_validate.py skills/docara
bash tests/smoke.sh
python3 scripts/mirai_graph_contract_gate.py
```

The repository contains no project initializer or theme generator of its own:
the Docara CLI and product schemas are the executable source of truth.
