# Hookify

Web application and dashboard for configuring project-specific Git hooks and developer workflows.

Hookify lets a team pick pre-commit and commit-msg checks from a fixed catalog (Laravel Pint, PHPStan, OXC — oxlint/oxfmt, ESLint/Prettier, gitleaks, commitlint) in a visual constructor, then keeps every repository's local hooks in sync with a single CLI command. No more copy-pasting shell scripts between projects or watching hook configs drift between teammates.

This is the web/API half of the project. The CLI client lives in [hookify-cli](https://github.com/BarneyMayerson/hookify-cli).

## Why

Setting up pre-commit hooks by hand — linting, formatting, secret scanning, commit message conventions — is fiddly to configure correctly and easy to let rot across a team's repositories. Hookify centralizes the configuration in one place and lets a CLI pull the current setup into any repo's `.git/hooks` on demand.

## Stack

- Laravel · Inertia.js · Vue 3 (Composition API, `<script setup>`) · TypeScript · Tailwind CSS
- Auth via GitHub OAuth only (no passwords)
- Pest for tests, PHPStan for static analysis, [vite-plus](https://github.com/voidzero-dev/vite-plus) (oxlint/oxfmt) for linting and formatting

## Local development

```bash
git clone https://github.com/BarneyMayerson/hookify.git
cd hookify
cp .env.example .env
sail up -d
sail artisan migrate
sail npm install
sail npm run dev
```

You'll need a GitHub OAuth App configured with its callback URL pointing at `{APP_URL}/auth/github/callback`, and `GITHUB_CLIENT_ID`/`GITHUB_CLIENT_SECRET` set in `.env`.

## How checks work

`App\Support\ChecksCatalog` is the single source of truth for every available check — its command, which hook type it belongs to (`pre-commit` or `commit-msg`), and whether it needs something the project doesn't ship with (a config file, or an external binary like `gitleaks`). A project's enabled checks live in `project_rules`; the `/api/v1/manifest` endpoint turns that into the JSON the CLI consumes to write actual hook files.

## Status

Early and actively developed — see the open issues for what's next. Not affiliated with GitHub.

## License

MIT — see [LICENSE](LICENSE).
