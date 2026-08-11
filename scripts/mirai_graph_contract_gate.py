#!/usr/bin/env python3
"""Compatibility entry point for the active Mirai Graph runtime contract."""

from __future__ import annotations

import runpy
from pathlib import Path


runpy.run_path(str(Path(__file__).with_name("validate_skill_contract.py")), run_name="__main__")
