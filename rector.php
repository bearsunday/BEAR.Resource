<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/src-files',
        __DIR__ . '/tests/*Test.php',
        __DIR__ . '/tests-php8/*Test.php',
    ]);
    $rectorConfig->skip([
       __DIR__ . '/src/*Interface.php'
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);
    $rectorConfig->skip([
        FirstClassCallableRector::class,
        ArraySpreadInsteadOfArrayMergeRector::class
    ]);
};
