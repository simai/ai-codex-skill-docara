#!/usr/bin/env python3
"""Validate the canonical Docara skill and its compact runtime graph."""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SKILL = ROOT / "skills/docara/SKILL.md"
INDEX = ROOT / "graph/specs/index.json"
EXPECTED_REFERENCES = {
    "architecture-and-project-model.md",
    "settings-and-inheritance.md",
    "content-locales-and-navigation.md",
    "components-smart-and-design.md",
    "build-preview-and-verification.md",
    "developer-sdk-and-qa.md",
    "update-release-and-publication.md",
    "security-and-troubleshooting.md",
}
FORBIDDEN = ("Jigsaw", "Laravel Mix", "source/_core", ".settings.php", "init --portable")


def load_json(path: Path) -> object:
    with path.open(encoding="utf-8") as handle:
        return json.load(handle)


def main() -> int:
    blockers: list[str] = []
    text = SKILL.read_text(encoding="utf-8")
    match = re.match(r"\A---\n(.*?)\n---\n", text, re.DOTALL)
    if not match:
        blockers.append("SKILL.md has no valid front matter")
    else:
        keys = [line.split(":", 1)[0] for line in match.group(1).splitlines() if ":" in line]
        if keys != ["name", "description"]:
            blockers.append(f"SKILL.md front matter keys must be name, description; got {keys}")
    if "name: docara" not in text:
        blockers.append("SKILL.md name is not docara")

    reference_dir = SKILL.parent / "references"
    actual_references = {path.name for path in reference_dir.glob("*.md")}
    if actual_references != EXPECTED_REFERENCES:
        blockers.append(
            "reference set differs: "
            f"missing={sorted(EXPECTED_REFERENCES - actual_references)} "
            f"extra={sorted(actual_references - EXPECTED_REFERENCES)}"
        )
    for name in EXPECTED_REFERENCES:
        if f"./references/{name}" not in text:
            blockers.append(f"SKILL.md does not route to {name}")

    source_paths = [SKILL, *sorted(reference_dir.glob("*.md")), *sorted((SKILL.parent / "rules").glob("*.md"))]
    for path in source_paths:
        body = path.read_text(encoding="utf-8")
        for token in FORBIDDEN:
            if token in body:
                blockers.append(f"legacy token {token!r} remains in {path.relative_to(ROOT)}")

    try:
        index = load_json(INDEX)
        assert isinstance(index, dict)
    except Exception as exc:  # noqa: BLE001
        blockers.append(f"graph index is invalid: {exc}")
        index = {}

    objects: dict[str, dict] = {}
    for relative in index.get("object_files", []):
        path = INDEX.parent / relative
        try:
            value = load_json(path)
            assert isinstance(value, dict)
            object_id = value["id"]
            if object_id in objects:
                blockers.append(f"duplicate graph object id: {object_id}")
            objects[object_id] = value
            for source_ref in value.get("source_refs", []):
                if not (ROOT / source_ref).exists():
                    blockers.append(f"missing source_ref {source_ref} in {relative}")
        except Exception as exc:  # noqa: BLE001
            blockers.append(f"invalid graph object {relative}: {exc}")

    capabilities = {key for key in objects if key.startswith("capability.docara.")}
    if len(capabilities) != 8:
        blockers.append(f"expected 8 Docara capabilities, got {len(capabilities)}")

    relation_ids: set[str] = set()
    for relative in index.get("relation_files", []):
        path = INDEX.parent / relative
        try:
            value = load_json(path)
            assert isinstance(value, dict)
            relation_id = value["id"]
            if relation_id in relation_ids:
                blockers.append(f"duplicate graph relation id: {relation_id}")
            relation_ids.add(relation_id)
            if value.get("from") not in objects or value.get("to") not in objects:
                blockers.append(f"unresolved graph relation endpoints in {relative}")
        except Exception as exc:  # noqa: BLE001
            blockers.append(f"invalid graph relation {relative}: {exc}")

    for path in ROOT.rglob("*.json"):
        if "graph/generated" in path.as_posix():
            continue
        try:
            load_json(path)
        except Exception as exc:  # noqa: BLE001
            blockers.append(f"invalid JSON {path.relative_to(ROOT)}: {exc}")

    recipe_root = SKILL.parent / "repository-recipes"
    allowed_ownership = {"managed", "owner_template", "user_owned", "local_only"}
    for path in sorted(recipe_root.glob("*/recipe.json")):
        relative = path.relative_to(ROOT)
        try:
            recipe = load_json(path)
            assert isinstance(recipe, dict)
            if recipe.get("schema_version") != "2.0.0":
                blockers.append(f"unsupported recipe schema in {relative}")
            if recipe.get("owner") != "docara":
                blockers.append(f"recipe owner is not docara in {relative}")
            for group in ("entrypoints", "processes", "checks"):
                for source_ref in (recipe.get("source_contract") or {}).get(group, []):
                    if not (ROOT / source_ref).exists():
                        blockers.append(f"missing recipe source ref {source_ref} in {relative}")
            for item in recipe.get("file_manifest", []):
                if item.get("ownership") not in allowed_ownership:
                    blockers.append(f"invalid recipe ownership in {relative}: {item.get('ownership')}")
                manifest_path = str(item.get("path") or "")
                if not manifest_path or manifest_path.startswith(("/", "\\")) or ".." in Path(manifest_path).parts:
                    blockers.append(f"unsafe recipe path in {relative}: {manifest_path}")
        except Exception as exc:  # noqa: BLE001
            blockers.append(f"invalid recipe {relative}: {exc}")

    result = {
        "operation": "docara.skill-contract.validate",
        "status": "blocked" if blockers else "success",
        "skill": "docara",
        "references": len(actual_references),
        "graph_objects": len(objects),
        "graph_relations": len(relation_ids),
        "repository_recipes": len(list(recipe_root.glob("*/recipe.json"))),
        "blockers": blockers,
    }
    print(json.dumps(result, indent=2))
    return 1 if blockers else 0


if __name__ == "__main__":
    sys.exit(main())
