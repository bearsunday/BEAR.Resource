# BEAR SchemaLogger Schemas

This directory contains JSON Schema definitions for metadata structure in BEAR applications.

## Schema Convention

Each schema file corresponds to a `sessionId` used in `SchemaLogContext`:

```php
// sessionId: "resource_request" → schema: "resource_request.json"
$context = new SchemaLogContext('resource_request', $metadata);
```

## Available Schemas

### resource_request.json
For BEAR resource invocations:
```php
use BEAR\SchemaLogger\BearSchemas;
use Koriym\SchemaLogger\SchemaLogContext;

$context = new SchemaLogContext(
    BearSchemas::RESOURCE_REQUEST,
    [
        'uri' => 'app://self/user',
        'method' => 'get',
        'query' => ['id' => 123],
        'type' => 'resource_request'
    ]
);
```

### api_call.json  
For HTTP API calls:
```php
$context = new SchemaLogContext(
    BearSchemas::API_CALL,
    [
        'endpoint' => '/api/users',
        'method' => 'POST',
        'request_size' => 1024,
        'client_ip' => '192.168.1.1'
    ]
);
```

### database_query.json
For database operations:
```php
$context = new SchemaLogContext(
    BearSchemas::DATABASE_QUERY,
    [
        'query' => 'SELECT * FROM users WHERE id = ?',
        'parameters' => [123],
        'execution_time' => 0.025,
        'affected_rows' => 1
    ]
);
```

## Schema Validation

This library does not perform validation but provides these schemas for:
- Development-time reference
- External validation tools
- Documentation
- AI analysis tools

## Adding Custom Schemas

1. Create `{sessionId}.json` file in this directory
2. Follow JSON Schema Draft 7 specification
3. Use the schema with matching `sessionId` in your code