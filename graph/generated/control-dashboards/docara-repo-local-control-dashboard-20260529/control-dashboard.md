# Graph Control Dashboard control-dashboard.docara-repo-local-control-dashboard-20260529

- Graph: `example.graph`
- Status: `control_ready`
- Canonical state: `sha256:a29f6efa731728fc877856267e5760e95cee578ba2a42f48f49bed1cda10fe6f`
- Headline: Seed and embryo are available; use projection views and score reports for next decision.

## Control Surfaces

| Surface | Status | Ref |
| --- | --- | --- |
| `seed` | `success` | `graph/source/growgraph/seeds/docara-repo-local-seed-20260529.json` |
| `embryo` | `available` | `graph/generated/seed-expansions/docara-repo-local-seed-expand-20260529/graph-embryo.json` |
| `readiness` | `missing` | `` |
| `quality` | `missing` | `` |
| `proposal_queue` | `available` | `graph/generated/embryo-proposals/docara-repo-local-embryo-proposals-20260529/action-proposals.json` |

## Embryo Summary

- Candidate objects: `36`
- Candidate relations: `32`
- Source files: `7`
- Average confidence: `0.513`
- Review groups: `8`
- Duplicate groups: `0`

## Projection Views

- `seed_summary`: `graph/generated/seed-expansions/docara-repo-local-seed-expand-20260529/projection-view.json`

## Stale Artifacts

- none

## Next Actions

- run graph readiness-score for the target mode
- run graph quality-review or quality-dashboard when content quality matters
- review pending proposal actions before canonical write
