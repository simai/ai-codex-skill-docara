#!/usr/bin/env python3
"""Portable launcher for the active Mirai Graph skill runtime."""

from __future__ import annotations

import os
import runpy
import sys
from pathlib import Path


def installed_kit_root() -> Path | None:
    codex_home = Path(os.environ.get("CODEX_HOME", str(Path.home() / ".codex"))).expanduser()
    marker = codex_home / "simai-workspace" / "install.env"
    if not marker.is_file():
        return None
    for raw in marker.read_text(encoding="utf-8-sig").splitlines():
        key, separator, value = raw.partition("=")
        if separator and key.strip() == "KIT_ROOT":
            return Path(value.strip().strip("'\"")).expanduser()
    return None


def resolve_runtime() -> Path:
    explicit = os.environ.get("MIRAI_GRAPH_RUNTIME_KIT")
    repo = Path(__file__).resolve().parents[1]
    codex_home = Path(os.environ.get("CODEX_HOME", str(Path.home() / ".codex"))).expanduser()
    candidates = [Path(explicit).expanduser()] if explicit else []
    candidates.append(repo.parent / "ai-codex-skill-graph" / "skills" / "graph" / "scripts" / "mirai_graph_skill_runtime.py")
    kit_root = installed_kit_root()
    if kit_root:
        candidates.append(
            kit_root.parent / "ai-codex-skill-graph" / "skills" / "graph" / "scripts" / "mirai_graph_skill_runtime.py"
        )
    candidates.append(
        codex_home
        / "simai-workspace"
        / "runtime-current"
        / "workspace"
        / "ai-codex-skill-graph"
        / "skills"
        / "graph"
        / "scripts"
        / "mirai_graph_skill_runtime.py"
    )
    for candidate in candidates:
        if candidate.is_file():
            return candidate
    raise SystemExit("Mirai Graph runtime kit script not found in explicit, sibling, or active runtime locations")


runtime = resolve_runtime()
sys.argv = [str(runtime), *sys.argv[1:]]
runpy.run_path(str(runtime), run_name="__main__")
