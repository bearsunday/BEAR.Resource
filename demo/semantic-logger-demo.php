<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use BEAR\Resource\ResourceInterface;
use BEAR\SchemaLogger\Module\SchemaLoggerModule;
use FakeVendor\Sandbox\Module\AppModule;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Ray\Di\Injector;

// Create injector with semantic logger integration
// Create a combined module that installs SchemaLoggerModule AFTER AppModule
$combinedModule = new class extends \Ray\Di\AbstractModule {
    protected function configure(): void
    {
        $this->install(new AppModule);
        $this->install(new SchemaLoggerModule);
    }
};

$injector = new Injector($combinedModule);
$resource = $injector->getInstance(ResourceInterface::class);
$semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);

echo "=== BEAR.Resource + Semantic Logger Demo ===\n\n";

// Example 1: Simple resource request
echo "1. Simple resource request:\n";
$user = $resource->get('app://self/bird/canary');
echo "Result: {$user->body['name']}\n";

$logJson = $semanticLogger->flush();
echo "Semantic Log:\n";
echo json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Example 2: Resource with parameters  
echo "2. Resource with parameters:\n";
$sparrow = $resource->get('app://self/bird/sparrow', ['id' => '999']);
echo "Result: sparrow_id = {$sparrow->body['sparrow_id']}\n";

$logJson = $semanticLogger->flush();
echo "Semantic Log:\n";
echo json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Example 3: Resource with @Embed
echo "3. Resource with @Embed (complex operation):\n";
$birds = $resource->get('app://self/bird/birds', ['id' => '123']);
echo "Result: bird1 = {$birds->body['bird1']['name']}, bird2 = sparrow_id {$birds->body['bird2']['sparrow_id']}\n";

$logJson = $semanticLogger->flush();
echo "Semantic Log:\n";
echo json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "=== Demo Complete ===\n";
echo "Semantic logger successfully integrated with BEAR.Resource!\n";
echo "Every resource request is now automatically logged with structured data.\n";