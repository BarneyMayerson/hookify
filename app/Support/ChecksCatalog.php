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
 * }
 */
class ChecksCatalog
{
    /**
     * Фиксированный список — пользователь включает/выключает готовые чеки
     * тогглами, а не вводит свои команды, поэтому это код, а не таблица БД.
     *
     * requiresConfig=true — чек не работает "из коробки" на дефолтах,
     * ему нужен собственный конфиг-файл в целевом репозитории (иначе
     * первый же коммит упадёт с ошибкой конфигурации, а не находкой стиля).
     * Конструктор должен явно предупреждать об этом при включении.
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
            ],
            'phpstan' => [
                'label' => 'PHPStan',
                'ecosystem' => 'php',
                'command' => 'vendor/bin/phpstan analyse --no-progress',
                'tier' => 'primary',
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
            ],
            'oxlint' => [
                'label' => 'OXC — oxlint',
                'ecosystem' => 'js',
                'command' => 'npx --no-install oxlint --deny-warnings',
                'tier' => 'primary',
                'default' => true,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
            ],
            'oxfmt' => [
                'label' => 'OXC — oxfmt',
                'ecosystem' => 'js',
                'command' => 'npx --no-install oxfmt --check .',
                'tier' => 'primary',
                'default' => true,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
            ],
            'eslint' => [
                'label' => 'ESLint',
                'ecosystem' => 'js',
                'command' => 'npx --no-install eslint .',
                'tier' => 'secondary',
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
            ],
            'prettier' => [
                'label' => 'Prettier',
                'ecosystem' => 'js',
                'command' => 'npx --no-install prettier --check .',
                'tier' => 'secondary',
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
            ],
            'gitleaks' => [
                'label' => 'Secret scanning (gitleaks)',
                'ecosystem' => 'universal',
                'command' => 'gitleaks protect --staged --redact',
                'tier' => 'primary',
                'default' => true,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
            ],
            'commitlint' => [
                'label' => 'Commit message format (commitlint)',
                'ecosystem' => 'universal',
                // $1 — путь к файлу с сообщением коммита, его передаёт сам git
                // при вызове commit-msg хука, а не CLI Hookify.
                'command' => 'npx --no-install commitlint --edit "$1"',
                'tier' => 'primary',
                'default' => false,
                'hook' => 'commit-msg',
                'requiresConfig' => true,
            ],
        ];
    }

    public static function isValid(string $id): bool
    {
        return array_key_exists($id, self::all());
    }

    /**
     * Id чеков, включённых по умолчанию для нового проекта.
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
     * Фильтрует каталог по переданным id, сохраняя порядок каталога
     * (не порядок, в котором id пришли извне) — манифест должен быть
     * детерминированным независимо от порядка строк в project_rules.
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
