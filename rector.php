<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Ray\AnnotationBinding\Rector\ClassMethod\AnnotationBindingRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/demo',
        __DIR__ . '/src',
        __DIR__ . '/src-files',
        __DIR__ . '/tests',
        __DIR__ . '/tests-php8',
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(AnnotationBindingRector::class);
    // define sets of rules
        $rectorConfig->sets([
            LevelSetList::UP_TO_PHP_80,
    ]);
};
