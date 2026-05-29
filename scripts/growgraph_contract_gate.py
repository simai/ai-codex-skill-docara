#!/usr/bin/env python3
"""Validate the repo-local GrowGraph integration contract."""

from __future__ import annotations

import json
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SKILL = ROOT.name.removeprefix("ai-codex-skill-")
RUN_DATE = "20260529"


REQUIRED_ARTIFACTS = {
    "semantic": Path(f"graph/generated/growgraph/semantic-preservation/{SKILL}-repo-local-semantic-review-{RUN_DATE}/semantic-preservation-verdict.json"),
    "effectiveness": Path(f"graph/generated/growgraph/effectiveness-reports/{SKILL}-repo-local-effectiveness-{RUN_DATE}/effectiveness-report.json"),
    "adoption": Path(f"graph/generated/growgraph/adoption-reports/{SKILL}-repo-local-adoption-gga9-active-gate-{RUN_DATE}/growgraph-adoption-report.json"),
    "federation": Path(f"graph/federation/exports/{SKILL}-repo-local-federation-export-{RUN_DATE}/federation-export.json"),
    "integration": Path(f"graph/generated/growgraph/integration-reviews/{SKILL}-repo-local-integration-review-active-gate-{RUN_DATE}/growgraph-integration-review.json"),
}


def load_json(ref: Path) -> dict:
    path = ROOT / ref
    if not path.exists():
        raise FileNotFoundError(str(ref))
    with path.open(encoding="utf-8") as fh:
        return json.load(fh)


def main() -> int:
    blockers: list[str] = []
    artifacts: dict[str, dict] = {}
    for name, ref in REQUIRED_ARTIFACTS.items():
        try:
            artifacts[name] = load_json(ref)
        except Exception as exc:  # noqa: BLE001
            blockers.append(f"{name}: {exc}")
    if artifacts.get("semantic", {}).get("verdict") not in {"pass", "pass_with_notes"}:
        blockers.append("semantic preservation did not pass")
    if artifacts.get("effectiveness", {}).get("verdict") != "improved":
        blockers.append("effectiveness verdict is not improved")
    if artifacts.get("adoption", {}).get("adoption_level") != 9:
        blockers.append("adoption level is not GGA9")
    federation = artifacts.get("federation", {})
    if federation.get("protocol_version") != "1.0.0" or federation.get("manifest", {}).get("graph_type") != "growgraph_skill_pilot":
        blockers.append("federation export is missing or invalid")
    if artifacts.get("integration", {}).get("integration_allowed") is not True:
        blockers.append("integration review is not allowed")
    print(json.dumps({
        "operation_id": f"{SKILL}.growgraph-contract-gate",
        "status": "blocked" if blockers else "success",
        "blockers": blockers,
        "checked_artifacts": {name: str(ref) for name, ref in REQUIRED_ARTIFACTS.items()},
        "canonical_write_allowed": False,
    }, ensure_ascii=False, indent=2))
    return 1 if blockers else 0


if __name__ == "__main__":
    sys.exit(main())
