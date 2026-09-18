<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withRules([
        DeclareStrictTypesRector::class,
    ])
    ->withSkip([
        AddClosureVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withPhpSets(php85: true)
    ->withTypeCoverageLevel(5)
    ->withDeadCodeLevel(5)
    ->withCodeQualityLevel(5);
