<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Injector;
use Koriym\SemanticLogger\SemanticLoggerInterface;

$injector = new Injector(new TestModule());
$resource = $injector->getInstance(ResourceInterface::class);
$semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);

$resource->get('app://self/simple', ['id' => 'test-123']);

$logOutput = $semanticLogger->flush();

file_put_contents(
    __DIR__ . '/simple-semantic-log.json',
    json_encode($logOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);