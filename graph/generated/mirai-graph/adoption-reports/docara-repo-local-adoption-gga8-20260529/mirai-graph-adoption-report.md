# Mirai Graph Adoption Report mirai-graph-adoption-report.docara-repo-local-adoption-gga8-20260529

- Target: `skill:docara`
- Owner skill: `docara`
- Adoption: `GGA8` `federation_ready`
- Status: `blocked`
- Headline: Mirai Graph adoption is at GGA8; blockers must be cleared before migration claim.
- GRS: `None`
- CQS: `None`
- Semantic verdict: `pass_with_notes`
- Effectiveness verdict: `improved`

## Checks

| Check | Status |
| --- | --- |
| `source_inventory_done` | `pass` |
| `seed_validated` | `pass` |
| `embryo_generated` | `pass` |
| `control_dashboard_generated` | `pass` |
| `grs_generated` | `unknown` |
| `cqs_generated` | `unknown` |
| `proposal_gate_available` | `pass` |
| `semantic_preservation_passed` | `pass` |
| `effectiveness_checked` | `pass` |
| `federation_export_validated` | `pass` |
| `agents_integration_reviewed` | `unknown` |
| `hooks_integration_reviewed` | `unknown` |

## Blockers

- `graph`: AGENTS.md and hooks integration review has not passed -> run mirai-graph-integration-review with durable AGENTS.md and hook/CI refs

## Next Actions

- run GRS/CQS or target scoring gate
- run mirai-graph-adoption-report after each gate
