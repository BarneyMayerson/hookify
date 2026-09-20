<?php

use App\Support\ChecksCatalog;

describe('ChecksCatalog', function () {
    it('gives every check a label, ecosystem, command and tier', function () {
        foreach (ChecksCatalog::all() as $check) {
            expect($check)->toHaveKeys(['label', 'ecosystem', 'command', 'tier', 'default'])
                ->and($check['ecosystem'])->toBeIn(['php', 'js', 'universal'])
                ->and($check['tier'])->toBeIn(['primary', 'secondary']);
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

    it('filters by id preserving catalog order regardless of input order', function () {
        $filtered = ChecksCatalog::forIds(['oxfmt', 'pint']);

        expect(array_keys($filtered))->toBe(['pint', 'oxfmt']);
    });

    it('silently drops unknown ids instead of erroring', function () {
        $filtered = ChecksCatalog::forIds(['pint', 'does-not-exist']);

        expect(array_keys($filtered))->toBe(['pint']);
    });
});
