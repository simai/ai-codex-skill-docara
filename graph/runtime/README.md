# Mirai Graph Skill Runtime Kit: docara

This repository uses Mirai Graph as a graph-first routing and capability
context layer. Raw skill files remain authoritative for domain meaning.

Useful commands:

```bash
python scripts/mirai_graph_skill_runtime.py verify .
python scripts/mirai_graph_skill_runtime.py context . --task "Run representative task"
python scripts/mirai_graph_contract_gate.py
```

Generated runtime context lives in `graph/generated/mirai-graph/runtime-context/`.
It does not permit canonical writes or source rewrites.
