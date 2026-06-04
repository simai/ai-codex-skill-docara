#!/usr/bin/env python3
"""Repo-local Mirai Graph Skill Runtime Kit wrapper."""

from __future__ import annotations

import runpy
import os
import sys
from pathlib import Path

DEFAULT_RUNTIME = Path('/Users/rim/Documents/GitHub/ai-codex-skill-graph/skills/graph/scripts/mirai_graph_skill_runtime.py')
RUNTIME = Path(os.environ.get('MIRAI_GRAPH_RUNTIME_KIT', str(DEFAULT_RUNTIME))).expanduser()
if not RUNTIME.exists():
    raise SystemExit(f'Mirai Graph runtime kit script not found: {RUNTIME}')
sys.argv = [str(RUNTIME), *sys.argv[1:]]
runpy.run_path(str(RUNTIME), run_name='__main__')
