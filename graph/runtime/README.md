# GrowGraph Skill Runtime Kit: docara

This repository uses GrowGraph as a graph-first routing and capability
context layer. Raw skill files remain authoritative for domain meaning.

Useful commands:

```bash
python scripts/growgraph_skill_runtime.py verify .
python scripts/growgraph_skill_runtime.py context . --task "Run representative task"
python scripts/growgraph_contract_gate.py
```

Generated runtime context lives in `graph/generated/growgraph/runtime-context/`.
It does not permit canonical writes or source rewrites.
