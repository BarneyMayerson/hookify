# Contributing to Hookify

Thanks for considering a contribution! Hookify is a small, solo-maintained open source project, so a few ground rules keep things fast for everyone.

This repository is the **web app and API**. The CLI lives in [hookify-cli](https://github.com/BarneyMayerson/hookify-cli) — CLI changes go there.

## Before you start

- For anything bigger than a bug fix or a typo, **open an issue first** so we can agree on the approach before you invest time.
- Check the roadmap context in the README. A few things are deliberately out of scope for now: paid tiers or any kind of gated functionality, user-defined check commands, GitLab/Bitbucket support, and monorepo support.

## Setup

Follow the local development steps in the [README](README.md). You'll need your own GitHub OAuth App — login is GitHub-only, there are no passwords.

## Before opening a pull request

The project's checks are wrapped in Composer scripts. (Prefix commands with `sail` if you use Sail; otherwise run them directly.)

**Auto-fix first.** This rewrites files, so look at the diff afterwards:

```bash
sail composer lint        # Rector + Pint + frontend auto-fix (oxlint/oxfmt)
```

**Run individual checks** while you work:

```bash
sail composer pint:test       # PHP formatting, check only (`composer pint` fixes)
sail composer rector:dry      # Rector dry run
sail composer phpstan         # static analysis
sail composer wayfinder       # regenerate route helpers — run after touching routes/controllers
sail composer frontend:check  # oxlint + oxfmt + vue-tsc (`composer frontend:fix` fixes)
sail artisan test             # Pest suite only
```

**Then run everything in one go.** A PR should pass this locally:

```bash
sail composer test
```

It clears the config cache, runs the full non-modifying lint suite (Rector dry run, Pint, PHPStan, frontend checks), a PHPStan type check, and finally the Pest test suite.

Keep PRs small and focused — one concern per PR. Write clear commit messages; Conventional Commits are welcome but not required.

## Code conventions

**Language.** UI text, code comments, and docs are in English.

**PHP / Laravel**

- `Model::unguard()` is enabled app-wide, so models have no `$fillable`. That means **never pass raw request data to `create()`/`update()`** — always validate first and use the validated array.
- Ownership checks use `abort_unless($project->user_id === $request->user()->id, 404)`. We return **404, not 403**, so we don't confirm that another user's record exists.
- API tokens are stored as hashes only; `users.github_token` is `encrypted`-cast and `#[Hidden]`. Don't weaken either.
- Anything user-supplied that ends up in a generated shell hook (currently the project name) must be validated or sanitized — hooks execute on every commit.

**Frontend**

- Vue 3 with `<script setup lang="ts">`, Tailwind CSS, Inertia.
- Forms use `useForm` from `@inertiajs/vue3`.
- Use [Wayfinder](https://github.com/laravel/wayfinder)-generated route functions (`import { index } from '@/routes/projects'` → `index().url`), not the `route()` helper.
- Shared TypeScript types live in `resources/js/types/`.

**Tests (Pest)**

- Import helpers as functions (`use function Pest\Laravel\actingAs;`) instead of using `$this->` — editors can't resolve `$this` inside Pest closures.
- Group related cases in a top-level `describe()` block per file.
- New behavior needs tests; so does a bug fix (a test that fails without your change).

## Adding or changing a check

Checks live in `App\Support\ChecksCatalog` — a fixed, developer-authored list. Users toggle them; they never type commands.

- **Commands must never be derived from user input.** A check's `command` is executed as a shell command inside a git hook.
- Set `default: true` only if the tool is guaranteed to be there with zero setup on a typical Laravel + Vite project (i.e. it ships through composer/npm). Anything that needs its own config file (`requiresConfig`) or a standalone binary (`requiresBinary`) must stay opt-in, so a fresh `hookify sync` never breaks someone's first commit.
- Pre-commit checks must be fast. Heavy checks (full test suites) don't belong in `pre-commit`.
- Update `tests/Unit/ChecksCatalogTest.php` — it deliberately locks the default set.

## Changing the manifest

`GET /api/v1/manifest` is a contract with published CLI versions. If you change its shape in a way older CLIs can't read, bump `schema_version` and coordinate the change with hookify-cli — the CLI refuses manifests with a version it doesn't know.

## Database changes

Add a **new migration** rather than editing an existing one.

## Reporting security issues

Please don't open a public issue for a vulnerability. Use GitHub's private vulnerability reporting (the **Security** tab → **Report a vulnerability**) instead.

## License

By contributing, you agree that your contributions are licensed under the [MIT License](LICENSE).
