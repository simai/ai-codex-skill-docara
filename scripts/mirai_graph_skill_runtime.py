#!/usr/bin/env python3
"""Delegate to the active federation's Mirai Graph Skill Runtime Kit."""

from __future__ import annotations

import os
import runpy
import sys
from pathlib import Path


def candidates() -> list[Path]:
    explicit = os.environ.get("MIRAI_GRAPH_RUNTIME_KIT")
    result = [Path(explicit).expanduser()] if explicit else []
    codex_root = Path(os.environ.get("CODEX_HOME", Path.home() / ".codex")).expanduser()
    install_env = codex_root / "simai-workspace/install.env"
    if install_env.exists():
        for line in install_env.read_text(encoding="utf-8").splitlines():
            if line.startswith("KIT_ROOT="):
                kit_root = Path(line.split("=", 1)[1].strip().strip('"\'')).expanduser()
                result.append(kit_root.parent / "ai-codex-skill-graph/skills/graph/scripts/mirai_graph_skill_runtime.py")
    result.append(codex_root / "simai-workspace/runtime-current/workspace/ai-codex-skill-graph/skills/graph/scripts/mirai_graph_skill_runtime.py")
    return result


runtime = next((path for path in candidates() if path.is_file()), None)
if runtime is None:
    raise SystemExit("Active Mirai Graph Skill Runtime Kit was not found")
sys.argv = [str(runtime), *sys.argv[1:]]
runpy.run_path(str(runtime), run_name="__main__")
