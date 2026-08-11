# Repository Instructions

Use English for every repository document, description, graph object, script
message, and generated source artifact. User-facing conversation may follow the
user's language.

This repository owns the installable `$docara` skill. It does not own the
Docara product. Resolve current behavior from the target project's exact
`simai/docara` installation and from `/Users/rim/Documents/GitHub/docara` when
that checkout is the explicitly selected product source.

Do not use an older installed `$docara` skill to maintain this repository.
Maintain the Hybrid Source of Truth instead:

- `skills/docara/SKILL.md` is the compact router and safety contract;
- `skills/docara/references/` contains current operational knowledge;
- `graph/specs/` exposes machine-readable capabilities, policies, and gates;
- product schemas, CLI help, code, and documentation remain authoritative for
  exact fields and behavior.

Do not copy central Mirai Graph schemas or historical generated evidence into
this repository. Generated reports are evidence, not canonical skill sources.

After meaningful source changes, run:

```bash
python3 /Users/rim/.codex/skills/.system/skill-creator/scripts/quick_validate.py skills/docara
python3 scripts/validate_skill_contract.py
bash tests/smoke.sh
```

Then use the active federation's Skill Sync Gate. Installing, enabling,
committing, publishing, or releasing the skill requires separate authority.
