# Docara Theme Workflow

Use this when a Docara site must receive a branded color theme from one base color.

Docara uses SIMAI UI `core.css` from `simai/ui`. The floating palette button is a runtime demonstrator and should not be the production source of truth. For published docs, generate deterministic CSS variable overrides and import them after SIMAI UI core styles.

## Generate Variables

From a Docara project root:

```bash
php <skill>/scripts/docara-theme-vars.php --seed=#E81123 --install
npm run prod
php vendor/bin/docara build production
```

For a contained Docara project:

```bash
php <skill>/scripts/docara-theme-vars.php --root=docara --seed=#E81123 --install
```

The script writes:

- `source/_core/_assets/css/_theme.generated.scss`
- an import line in `source/_core/_assets/css/main.scss`

The generated file overrides SIMAI UI variables such as:

- `--sf-primary-*`
- `--sf-secondary-*`
- `--sf-tertiary-*`
- `--sf-neutral-*`
- `--sf-info-*`
- alpha helpers like `--sf-primary-50--alfa-12`

By default, semantic status colors (`success`, `warning`, `error`) stay on SIMAI UI defaults. Use `--include-status` only when the project intentionally wants alerts and validation states to inherit the brand palette.

## Production Policy

Keep the generated theme file committed with the Docara source. Do not depend on browser-local theme-builder state, copied CSS from DevTools, or an untracked `.docara-state` file.

For public documentation, remove the floating palette icon. The standard branding script removes it by default:

```bash
php <skill>/scripts/docara-apply-branding.php --root=. --title='Project Name' --write
```

If cleaning manually, remove this line from `source/_core/_layouts/main.blade.php`:

```blade
<div data-theme-builder="drawer" right="c8" bottom="e1" class="sf-theme-builder"></div>
```

This line is useful while designing a theme, but public documentation should expose only reader controls such as theme mode, wide layout, language, search, and actions. Use `--keep-theme-builder` only for private design/debug stands.

## Verification

After applying a theme:

1. Run frontend build and production build.
2. Open at least one light-mode page and one dark-mode page.
3. Check active menu item, links, bottom navigation cards, settings menu, search input, and code blocks.
4. Confirm contrast is still readable. A single seed color can produce weak contrast if it is too light, too gray, or too close to neutral.

If the generated colors look too aggressive, choose a calmer seed or edit only the seed and regenerate. Do not hand-edit individual generated tones unless the project needs a bespoke palette.
