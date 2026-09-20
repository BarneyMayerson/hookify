<?php

use App\Support\ChecksCatalog;

describe('ChecksCatalog', function () {
    it('gives every check a label, ecosystem, command, tier, hook and requiresConfig', function () {
        foreach (ChecksCatalog::all() as $check) {
            expect($check)->toHaveKeys([
                'label', 'ecosystem', 'command', 'tier', 'default', 'hook', 'requiresConfig',
            ])
                ->and($check['ecosystem'])->toBeIn(['php', 'js', 'universal'])
                ->and($check['tier'])->toBeIn(['primary', 'secondary'])
                ->and($check['hook'])->toBeIn(['pre-commit', 'commit-msg']);
        }
    });

    it('validates known and unknown ids', function () {
        expect(ChecksCatalog::isValid('pint'))->toBeTrue()
            ->and(ChecksCatalog::isValid('does-not-exist'))->toBeFalse();
    });

    it('only defaults to primary OXC + Pint + gitleaks, not legacy ESLint/Prettier', function () {
        // Замок на осознанное решение дорожной карты: ESLint+Prettier — secondary,
        // не должны попадать в дефолт нового проекта без явного выбора пользователя.
        expect(ChecksCatalog::defaults())
            ->toBe(['pint', 'oxlint', 'oxfmt', 'gitleaks'])
            ->not->toContain('eslint')
            ->not->toContain('prettier');
    });

    it('does not default-enable commitlint, since it requires repo-level config', function () {
        expect(ChecksCatalog::defaults())->not->toContain('commitlint')
            ->and(ChecksCatalog::all()['commitlint']['requiresConfig'])->toBeTrue()
            ->and(ChecksCatalog::all()['commitlint']['hook'])->toBe('commit-msg');
    });

    it('filters by id preserving catalog order regardless of input order', function () {
        $filtered = ChecksCatalog::forIds(['oxfmt', 'pint']);

        expect(array_keys($filtered))->toBe(['pint', 'oxfmt']);
    });

    it('silently drops unknown ids instead of erroring', function () {
        $filtered = ChecksCatalog::forIds(['pint', 'does-not-exist']);

        expect(array_keys($filtered))->toBe(['pint']);
    });
});
