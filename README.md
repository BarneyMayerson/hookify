# Hookify

Open source web app that keeps Git hooks in one place and syncs them to every developer on your team. Live at **[hookify.to](https://hookify.to)**.

Hookify lets a team pick pre-commit and commit-msg checks from a fixed catalog (Laravel Pint, PHPStan, OXC — oxlint/oxfmt, ESLint/Prettier, gitleaks, commitlint) in a visual constructor, then keeps every repository's local hooks in sync with a single CLI command. No more copy-pasting shell scripts between projects or watching hook configs drift between teammates.

This is the web/API half of the project. The CLI client lives in [hookify-cli](https://github.com/BarneyMayerson/hookify-cli) ([npm](https://www.npmjs.com/package/hookify-cli)).

## How it works

1. Sign in with GitHub and create a project. Hookify generates a project token and shows it **once**.
2. Open the project's settings and enable the checks you need.
3. In your repository, run the command shown after creation:

```bash
HOOKIFY_TOKEN=hk_live_... npx hookify-cli sync
```

The CLI fetches the project's configuration and writes the matching `pre-commit` and `commit-msg` hooks into `.git/hooks`. Change the checks in the dashboard, run `sync` again, and every developer gets the same setup. The dashboard shows when each project last synced.

## Why

Setting up pre-commit hooks by hand — linting, formatting, secret scanning, commit message conventions — is fiddly to configure correctly and easy to let rot across a team's repositories. Hookify centralizes the configuration in one place and lets a CLI pull the current setup into any repo's `.git/hooks` on demand.

## Stack

- Laravel 13 · PHP 8.5 · MySQL 8.4
- Inertia.js · Vue 3 (Composition API, `<script setup>`) · TypeScript · Tailwind CSS
- Auth via GitHub OAuth only (no passwords)
- Pest for tests, PHPStan for static analysis, [vite-plus](https://github.com/voidzero-dev/vite-plus) (oxlint/oxfmt) for linting and formatting

## How checks work

`App\Support\ChecksCatalog` is the single source of truth for every available check: its command, which hook type it belongs to (`pre-commit` or `commit-msg`), and whether it needs something the project doesn't ship with (a config file, or an external binary like `gitleaks`). Users toggle ready-made checks on and off; there is deliberately no way to enter a custom command.

A project's enabled checks live in `project_rules`. The `/api/v1/manifest` endpoint, authenticated with the project token, turns them into a versioned JSON manifest (`schema_version`) that the CLI consumes to write the actual hook files. A CLI that doesn't know the manifest's schema version refuses to use it.

## Local development

```bash
git clone https://github.com/BarneyMayerson/hookify.git
cd hookify
cp .env.example .env
composer install            # no local PHP? see "Installing Composer dependencies" in the Laravel Sail docs
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate
vendor/bin/sail npm install
vendor/bin/sail npm run dev
```

The examples below use `sail` as a shorthand for `vendor/bin/sail`.

You'll need a GitHub OAuth App with its callback URL set to `{APP_URL}/auth/github/callback`, and `GITHUB_CLIENT_ID` / `GITHUB_CLIENT_SECRET` in `.env`. Use a separate OAuth App for each environment; GitHub allows only one callback URL per app.

```bash
sail artisan test            # Pest
sail bin phpstan analyse     # static analysis
```

### Trying the CLI against your local instance

The dashboard builds the command from `APP_URL`, so set it to the address you actually use. For any host other than hookify.to the command includes `HOOKIFY_API` automatically:

```bash
HOOKIFY_TOKEN=hk_live_... HOOKIFY_API=http://localhost/api/v1 npx hookify-cli sync
```

If your local instance uses a certificate from a private CA (for example `mkcert`), add `NODE_EXTRA_CA_CERTS="$(mkcert -CAROOT)/rootCA.pem"`.

## Production

Hookify needs PHP 8.5, MySQL 8.4 and a reverse proxy that terminates TLS. A few things to get right:

- Set `APP_URL` to the public HTTPS URL. The dashboard builds the CLI commands and the OAuth callback from it.
- Trust the proxy so Laravel sees the original scheme and client IP (`$middleware->trustProxies(at: '*')` in `bootstrap/app.php` when the app is only reachable through your proxy).
- Run `php artisan migrate --force` on every deploy and keep `APP_DEBUG=false`.
- Provide `APP_KEY`, the `DB_*` settings and the GitHub OAuth credentials through the environment.

A production `Dockerfile` is included as a starting point.

## Security

- Project tokens are stored only as a hash (plus a short prefix for display). The full token is shown once, right after the project is created, and cannot be recovered.
- Hook commands come only from the fixed catalog, never from user input. A project's name is the only free text that ends up in a generated hook, and the CLI sanitizes it.
- The hooks run commands on developers' machines at every commit, so the manifest endpoint is a sensitive surface. If you run your own instance, keep it up to date and behind HTTPS.
- Sign-in requests the GitHub scopes `user:email` and `repo`; `repo` is used to let you pick the repository a project is bound to.

Please report vulnerabilities privately through GitHub's [security advisories](https://github.com/BarneyMayerson/hookify/security/advisories/new) rather than in a public issue.

## Status

Early and actively developed. See the open issues for what's next. Not affiliated with GitHub.

## License

MIT, see [LICENSE](LICENSE).
