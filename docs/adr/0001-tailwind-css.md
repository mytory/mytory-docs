# ADR 0001: Tailwind CSS over Bootstrap 3 / plain CSS

Bootstrap 3 + jQuery was the original styling layer when this project started (~2015). For modernization, **Tailwind CSS v4** was chosen over upgrading to Bootstrap 5 or rewriting in plain CSS.

Bootstrap 3 is EOL (last release 2019), requires jQuery for interactive components (modals, collapse navbar), and ships ~120KB of unused components. Tailwind eliminates JavaScript dependencies for UI components entirely — `<dialog>` replaces jQuery modals, a 3-line toggle replaces the collapse navbar. The `@tailwindcss/typography` plugin handles markdown rendering (`prose` class), matching what Bootstrap's content styles did with zero configuration.

Plain CSS was considered but rejected: the project already has 370 lines of custom CSS that handles typography (Korean font stack), print styles, heading counters, and dark mode. Rewriting all layout/component styles from scratch would be more work than adopting Tailwind's utility-first approach while preserving the existing CSS file for project-specific flourishes.

The build step (npx @tailwindcss/cli) adds ~80ms to the development cycle. The resulting CSS is ~15KB minified (vs 120KB Bootstrap).

**Status**: accepted
**Date**: 2026-07-08
