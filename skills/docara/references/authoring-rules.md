# Docara 2 authoring rules

## Content tree

```text
content/
  en/
    section.json
    index.md
    guide/
      section.json
      install.md
      install.page.json  # only when page-specific settings are needed
```

Each navigable page has one H1. Keep slugs, paths, anchors, JSON keys, component
IDs, fenced code, inline code and URLs stable unless structure is intentionally
changing.

`section.json` owns inherited title, order, navigation, layout, regions,
reading and search behavior. A page sidecar overrides one page. Human-facing
system strings belong to locale language packs, not shared manifests.

Use registered components and validate their props. Preserve component JSON
and fenced code while translating. Build and verify after each meaningful
batch, then check menus, breadcrumbs, contents, previous/next, search, themes,
locales and responsive behavior over HTTP.
