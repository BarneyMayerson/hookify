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
 * }
 */
class ChecksCatalog
{
    /**
     * Фиксированный список — пользователь включает/выключает готовые чеки
     * тогглами, а не вводит свои команды, поэтому это код, а не таблица БД.
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
            ],
            'phpstan' => [
                'label' => 'PHPStan',
                'ecosystem' => 'php',
                'command' => 'vendor/bin/phpstan analyse --no-progress',
                'tier' => 'primary',
                'default' => false,
            ],
            'oxlint' => [
                'label' => 'OXC — oxlint',
                'ecosystem' => 'js',
                'command' => 'npx --no-install oxlint --deny-warnings',
                'tier' => 'primary',
                'default' => true,
            ],
            'oxfmt' => [
                'label' => 'OXC — oxfmt',
                'ecosystem' => 'js',
                'command' => 'npx --no-install oxfmt --check .',
                'tier' => 'primary',
                'default' => true,
            ],
            'eslint' => [
                'label' => 'ESLint',
                'ecosystem' => 'js',
                'command' => 'npx --no-install eslint .',
                'tier' => 'secondary',
                'default' => false,
            ],
            'prettier' => [
                'label' => 'Prettier',
                'ecosystem' => 'js',
                'command' => 'npx --no-install prettier --check .',
                'tier' => 'secondary',
                'default' => false,
            ],
            'gitleaks' => [
                'label' => 'Secret scanning (gitleaks)',
                'ecosystem' => 'universal',
                'command' => 'gitleaks protect --staged --redact',
                'tier' => 'primary',
                'default' => true,
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
