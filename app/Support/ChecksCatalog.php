<?php

declare(strict_types=1);

namespace App\Support;

/**
 * @phpstan-type Check array{
 *     label: string,
 *     ecosystem: 'php'|'js'|'universal',
 *     command: string,
 *     tier: 'primary'|'secondary',
 *     default: bool,
 *     hook: 'pre-commit'|'commit-msg',
 *     requiresConfig: bool,
 *     requiresBinary: bool,
 * }
 */
final class ChecksCatalog
{
    /**
     * A fixed list — the user toggles ready-made checks on/off rather than
     * entering their own commands, so this is code, not a DB table.
     *
     * requiresConfig=true — the check needs its own config file (and,
     * typically, its own dependency in package.json/composer.json) in the
     * target repository — without it the very first commit fails with a
     * configuration error, not a style finding.
     *
     * requiresBinary=true — the check isn't part of the project's
     * node_modules/vendor; it's a standalone binary (e.g. a Go program)
     * that has to be installed on the system by hand — unlike
     * pint/oxlint/oxfmt, which are guaranteed available via composer/npm
     * on any project on this stack. That's exactly why such checks aren't
     * default: true — on a typical new project's first hookify sync the
     * binary isn't there yet, and the hook would fail every commit with
     * "command not found" instead of providing value.
     *
     * Both flags are a signal for the constructor to show a warning when
     * the check is enabled.
     *
     * @return array<string, Check>
     */
    public static function all(): array
    {
        return [
            'pint' => [
                'label' => 'Laravel Pint',
                'ecosystem' => 'php',
                'command' => 'vendor/bin/pint --dirty --test',
                'tier' => 'primary',
                'default' => true,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => false,
            ],
            'phpstan' => [
                'label' => 'PHPStan',
                'ecosystem' => 'php',
                'command' => 'vendor/bin/phpstan analyse --no-progress',
                'tier' => 'primary',
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => false,
            ],
            'oxlint' => [
                'label' => 'OXC — oxlint',
                'ecosystem' => 'js',
                'command' => 'npx --no-install oxlint --deny-warnings',
                'tier' => 'primary',
                'default' => true,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => false,
            ],
            'oxfmt' => [
                'label' => 'OXC — oxfmt',
                'ecosystem' => 'js',
                'command' => 'npx --no-install oxfmt --check .',
                'tier' => 'primary',
                'default' => true,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => false,
            ],
            'eslint' => [
                'label' => 'ESLint',
                'ecosystem' => 'js',
                'command' => 'npx --no-install eslint .',
                'tier' => 'secondary',
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => false,
            ],
            'prettier' => [
                'label' => 'Prettier',
                'ecosystem' => 'js',
                'command' => 'npx --no-install prettier --check .',
                'tier' => 'secondary',
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => false,
            ],
            'gitleaks' => [
                'label' => 'Secret scanning (gitleaks)',
                'ecosystem' => 'universal',
                'command' => 'gitleaks protect --staged --redact',
                'tier' => 'primary',
                // An external Go binary, not an npm/composer dependency —
                // can't be a default, or the first sync on a clean machine
                // breaks every commit with "gitleaks: not found".
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => true,
            ],
            'commitlint' => [
                'label' => 'Commit message format (commitlint)',
                'ecosystem' => 'universal',
                // $1 — the path to the commit message file, passed by git
                // itself when invoking the commit-msg hook, not by the
                // Hookify CLI.
                'command' => 'npx --no-install commitlint --edit "$1"',
                'tier' => 'primary',
                'default' => false,
                'hook' => 'commit-msg',
                'requiresConfig' => true,
                'requiresBinary' => false,
            ],
        ];
    }

    public static function isValid(string $id): bool
    {
        return array_key_exists($id, self::all());
    }

    /**
     * Ids of checks enabled by default for a new project.
     *
     * @return list<string>
     */
    public static function defaults(): array
    {
        return array_keys(array_filter(
            self::all(),
            static fn (array $check): bool => $check['default'],
        ));
    }

    /**
     * Filters the catalog by the given ids, preserving catalog order (not
     * the order the ids arrived in) — the manifest must be deterministic
     * regardless of row order in project_rules.
     *
     * @param  iterable<string>  $ids
     * @return array<string, Check>
     */
    public static function forIds(iterable $ids): array
    {
        $wanted = collect($ids)->all();

        return array_filter(
            self::all(),
            static fn (string $id): bool => in_array($id, $wanted, true),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
