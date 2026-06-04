# Mirai Graph Skill Runtime Kit Hook

Before claiming graph-first runtime readiness for this skill, run:

```bash
python scripts/mirai_graph_skill_runtime.py verify .
python scripts/mirai_graph_skill_runtime.py context . --task "Representative task"
python scripts/mirai_graph_contract_gate.py
```

The generated context is a routing/capability layer only. Owner skill
source files remain authoritative.
