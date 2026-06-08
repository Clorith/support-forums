# Project Instructions

## Scope of Changes

This repository is a local development environment for the WordPress.org Support Forums. **Only the following directories contain code we own and should modify:**

- `public_html/wp-content/themes/wporg-support-2024/` — the support forums theme
- `public_html/wp-content/plugins/support-forums/` — the core support forums plugin
- `public_html/wp-content/plugins/wporg-bbp-*/` — all plugins with the `wporg-bbp-` prefix

Everything else under `public_html/wp-content/` (bbPress, Jetpack, Gutenberg, etc.) is a third-party dependency. Do not modify those files.

## Build Systems

Each owned directory manages its own assets independently. Run build commands from within the respective directory, not from the project root.

| Directory | Build tool | Key commands |
|---|---|---|
| `themes/wporg-support-2024/` | Grunt (`package.json`) | `npm install`, then `grunt` |
| `plugins/support-forums/` | None | CSS/JS edited directly |
| `plugins/wporg-bbp-*/` | None (per plugin) | Check each plugin directory |

> Before assuming a plugin has no build step, check its directory for a `package.json` or `Gruntfile.js`.

## Further Reading

See `README.md` in the project root for environment setup, prerequisites, and how to run the local development environment.
