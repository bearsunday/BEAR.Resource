# BEAR.Resource Semantic Logging System

A comprehensive semantic logging system for BEAR.Resource that provides structured, JSON-based logging with complete profiling capabilities.

## Overview

The Semantic Logging system captures detailed execution context, performance metrics, and profiling data for BEAR.Resource operations. It supports multiple profile levels and integrates seamlessly with profiling tools like XHProf and Xdebug.

## Architecture

### Core Components

- **SemanticInvoker**: Main invoker that wraps resource calls with semantic logging
- **ContextFactory**: Creates structured context objects for different lifecycle events
- **Profile System**: Multi-level profiling with Compact, Verbose, and Dev profiles
- **DevSemanticInvoker**: Development-focused invoker with immediate file persistence

### Profile Levels

#### Compact Profile
- Basic execution context
- Minimal overhead for production
- Essential request/response data

#### Verbose Profile  
- Complete profiling data (XHProf, Xdebug, PHP backtrace)
- Detailed performance metrics
- Full context preservation

#### Dev Profile
- Extends Verbose Profile
- Immediate file persistence for MCP integration
- AI-assisted debugging support

## Installation & Configuration

### Basic Setup

```php
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new SemanticLoggerModule());
    }
}
```

### Development Setup with File Persistence

```php
use BEAR\Resource\SemanticLog\Module\DevSemanticLoggerModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        // Enable immediate file logging for development
        $logDirectory = '/path/to/log/directory';
        $this->install(new DevSemanticLoggerModule($logDirectory));
    }
}
```

## Usage Examples

### Basic Resource Call with Semantic Logging

```php
use BEAR\Resource\ResourceInterface;

class UserController
{
    public function __construct(
        private ResourceInterface $resource
    ) {}
    
    public function getUser(string $id): ResourceObject
    {
        // Automatically logged with semantic context
        return $this->resource->get('app://self/user', ['id' => $id]);
    }
}
```

### Manual Context Creation

```php
use BEAR\Resource\SemanticLog\ContextFactoryInterface;
use BEAR\Resource\Request;

class CustomLogger
{
    public function __construct(
        private ContextFactoryInterface $factory
    ) {}
    
    public function logCustomOperation(Request $request): void
    {
        $openContext = $this->factory->createOpenContext($request);
        // Custom logging logic
    }
}
```

## Log Structure

### Open Context (Request Start)
```json
{
  "timestamp": "2025-08-04T11:30:45.123456Z",
  "request": {
    "method": "GET",
    "uri": "app://self/user?id=123",
    "options": {}
  },
  "profile": {
    "php": {
      "version": "8.3.22",
      "memory_usage": 2048576,
      "peak_memory": 2097152
    }
  }
}
```

### Complete Context (Successful Response)
```json
{
  "timestamp": "2025-08-04T11:30:45.456789Z",
  "resource": {
    "code": 200,
    "headers": {},
    "body": {"id": "123", "name": "John Doe"}
  },
  "profile": {
    "xhprof": {
      "main()": {"ct": 1, "wt": 1234, "cpu": 1000, "mu": 456, "pmu": 512}
    },
    "xdebug": {
      "trace_file": "/tmp/xdebug_trace_abc123.xt",
      "function_count": 42
    },
    "php": {
      "backtrace": [...],
      "execution_time": 0.123,
      "memory_usage": 2560000
    }  
  }
}
```

### Error Context (Exception Handling)
```json
{
  "timestamp": "2025-08-04T11:30:45.789012Z",
  "exception": {
    "class": "BEAR\\Resource\\Exception\\ResourceNotFoundException", 
    "message": "Resource not found: app://self/nonexistent",
    "file": "/path/to/file.php",
    "line": 42
  },
  "exceptionAsString": "BEAR\\Resource\\Exception\\ResourceNotFoundException: Resource not found...",
  "profile": {
    "php": {
      "backtrace": [...],
      "memory_usage": 2048576
    }
  }
}
```

## Development Features

### MCP Integration

The Dev Profile enables Model Context Protocol (MCP) integration for AI-assisted debugging:

```php
// Automatic file creation for each request
$module = new DevSemanticLoggerModule('/tmp/semantic-logs');

// Files created: semantic-dev-{timestamp}-{pid}-{uniqid}.json
// Example: semantic-dev-2025-08-04_11-30-45-123456-12345-abc123.json
```

### File Naming Convention

Dev Profile creates uniquely named files to prevent conflicts in concurrent environments:
- `semantic-dev-{Y-m-d_H-i-s-u}-{process_id}-{unique_id}.json`
- Microsecond timestamps for precise ordering
- Process ID for multi-process safety
- Unique ID for additional collision prevention

## Performance Considerations

### Profile Level Selection

- **Production**: Use Compact Profile for minimal overhead
- **Staging**: Use Verbose Profile for detailed analysis
- **Development**: Use Dev Profile for immediate debugging access

### Graceful Degradation

The system gracefully handles missing profiling extensions:

```php
// XHProf not available
"xhprof": null

// Xdebug not available or trace fails
"xdebug": null

// PHP profiling always available
"php": {
  "backtrace": [...],
  "memory_usage": 2048576
}
```

## Testing

### Unit Tests

```bash
# Run semantic logging tests
./vendor/bin/phpunit tests/SemanticLogVerboseProfileTest.php

# Run dev profile tests  
./vendor/bin/phpunit tests/DevSemanticLoggerTest.php

# Run all semantic log related tests
./vendor/bin/phpunit tests/ --filter Semantic
```

### Integration Tests

The system includes comprehensive tests for:
- Profile data accuracy
- File persistence in Dev mode
- Error handling scenarios
- Extension availability detection

## Schema Validation

JSON Schema definitions are available in `docs/schema/`:
- `semantic-log.json` - Complete log structure
- `open-context.json` - Request start context
- `complete-context.json` - Successful response context  
- `error-context.json` - Exception handling context

## Future Roadmap

### Planned for Independent Package

This semantic logging system is designed for extraction into an independent package:

- **Package Name**: `bear/semantic-log` or `koriym/bear-semantic-log`
- **Dependencies**: Minimal BEAR.Resource integration points
- **Extensibility**: Plugin system for custom profile types
- **Persistence**: Multiple backend support (File, Database, Message Queue)

### Extension Points

- Custom context factories
- Alternative persistence strategies  
- Profile-specific filtering
- Real-time streaming capabilities

## Contributing

When contributing to the semantic logging system:

1. Follow existing Profile architecture patterns
2. Maintain graceful degradation for optional extensions
3. Add comprehensive tests for new features
4. Update JSON schemas for structural changes
5. Consider performance impact of new profile data

## Related Documentation

- [Profile System Overview](Profile/README.md)
- [MCP Server Integration](../../docs/plan/mcp-semantic-logger-server.md)
- [JSON Schema Definitions](../../docs/schema/)
- [AI Analysis Guide](../../docs/semantic-log-analysis-guide.md)