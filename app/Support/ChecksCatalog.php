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
     * Фиксированный список — пользователь включает/выключает готовые чеки
     * тогглами, а не вводит свои команды, поэтому это код, а не таблица БД.
     *
     * requiresConfig=true — чеку нужен собственный конфиг-файл (и, как
     * правило, собственная зависимость в package.json/composer.json)
     * в целевом репозитории — без него первый же коммит упадёт с ошибкой
     * настройки, а не находкой стиля.
     *
     * requiresBinary=true — чек не входит в node_modules/vendor проекта,
     * это отдельный бинарник (например, Go-программа), который нужно
     * поставить в систему вручную — в отличие от pint/oxlint/oxfmt,
     * гарантированно доступных через composer/npm любого проекта на этом
     * стеке. Именно поэтому такие чеки не входят в default: true — при
     * первом hookify sync у типичного нового проекта бинарника ещё нет,
     * и хук будет валить каждый коммит "command not found", а не пользой.
     *
     * Оба флага — сигнал конструктору показать предупреждение при включении.
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
                // Внешний Go-бинарник, не npm/composer-зависимость — не может
                // быть дефолтом, иначе первый sync на чистой машине ломает
                // все коммиты фразой "gitleaks: not found".
                'default' => false,
                'hook' => 'pre-commit',
                'requiresConfig' => false,
                'requiresBinary' => true,
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
                'requiresBinary' => false,
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
