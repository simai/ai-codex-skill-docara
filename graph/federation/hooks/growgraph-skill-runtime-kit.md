# GrowGraph Skill Runtime Kit Hook

Before claiming graph-first runtime readiness for this skill, run:

```bash
python scripts/growgraph_skill_runtime.py verify .
python scripts/growgraph_skill_runtime.py context . --task "Representative task"
python scripts/growgraph_contract_gate.py
```

The generated context is a routing/capability layer only. Owner skill
source files remain authoritative.
