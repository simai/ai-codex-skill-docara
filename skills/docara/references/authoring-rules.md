# Docara Authoring Rules

Use this when creating or restructuring documentation content.

## Directory Shape

Default:

```text
source/docs/
  en/
    .lang.php
    .settings.php
    index.md
    section/
      .settings.php
      page.md
```

Keep each locale structurally aligned unless the task is intentionally locale-specific.

## Markdown Page Template

```markdown
---
extends: _core._layouts.documentation
section: content
title: Page Title
description: Short page description
---

# Page Title
```

Translate human-facing `title` and `description`; do not translate `extends`, `section`, slugs, identifiers, or paths.

## `.settings.php`

Section settings drive menu order and labels:

```php
<?php
return [
    'title' => 'Section Title',
    'showInMenu' => true,
    'order' => 10,
    'menu' => [
        'intro' => 'Introduction',
        'setup' => 'Setup',
    ],
];
```

Rules:

- `menu` keys are slugs and must match child folders/pages.
- `menu` values are human-facing labels.
- `order` should leave gaps (`10`, `20`, `30`) for future insertion.
- `showInMenu=false` is useful for landing pages and service nodes.

## `.lang.php`

Use for UI labels and repeated interface text:

```php
<?php
return [
    'search' => 'Search',
    'edit article' => 'Edit article',
    'previous' => 'Previous',
    'next' => 'Next',
];
```

Translate values only. Keep keys stable because templates and JavaScript can depend on them.
For the standard Docara UI, include service labels used by header and bottom navigation: `actions`, `settings`, `edit article`, `report a bug`, `navigation`, `previous`, and `next`.

## Content Quality

- Write docs as publishable operator/developer material, not as a chat transcript.
- Keep pages focused and navigable; split large mixed pages into sections.
- Preserve fenced code blocks, inline code, URLs, anchors, Blade directives, custom Docara tags, and file paths.
- If a stable project rule emerges, place it in the appropriate docs page or `source/` artifact, not only in the final chat response.

## Verification

After authoring changes:

```bash
yarn prod || npm run prod
php vendor/bin/docara build production
```

Then inspect `build_production` for generated locale directories, `index.html`, asset paths, and obvious broken links.
