<?php

declare(strict_types=1);

use BEAR\Resource\SchemaLogger;
use BEAR\Resource\Uri;

require __DIR__ . '/../vendor/autoload.php';

// Create a SchemaLogger instance
$logger = new SchemaLogger();

// Create a sample URI
$uri = new Uri('app://self/user');
$uri->method = 'get';
$uri->query = ['id' => 123, 'name' => 'John'];

// Generate a request ID
$requestId = $logger->generateRequestId();

echo "=== SchemaLogger Demo ===\n";
echo "Request ID: {$requestId}\n";
echo "URI: {$uri}\n\n";

// Open the logging session
$logger->open($requestId, $uri);

// Add various log entries
$logger->add('database_query', [
    'query' => 'SELECT * FROM users WHERE id = ?',
    'params' => [123],
    'execution_time' => 0.0023,
]);

$logger->add('cache_hit', [
    'key' => 'user:123',
    'ttl' => 3600,
]);

$logger->add('validation', [
    'field' => 'email',
    'rule' => 'required|email',
    'passed' => true,
]);

$logger->add('response_prepared', [
    'code' => 200,
    'headers' => ['Content-Type' => 'application/json'],
]);

// Close the session and get the complete log
$logData = $logger->close();

echo "=== Complete Log Data ===\n";
echo json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "\n\n";

echo "=== Summary ===\n";
echo "Total entries: " . count($logData['entries']) . "\n";
echo "Request URI: " . $logData['context']['uri'] . "\n";
echo "Request method: " . $logData['context']['method'] . "\n";
echo "Request ID: " . $logData['context']['request_id'] . "\n";
echo "\nEvent types logged:\n";
foreach ($logData['entries'] as $entry) {
    echo "  - {$entry['type']}\n";
}