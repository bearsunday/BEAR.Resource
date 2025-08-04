# Semantic Profiler MCP Server

AI-powered performance analysis through structured semantic profiling.

## The Vision

Twenty years ago, Tim Berners-Lee envisioned the **Semantic Web** - a world where machines could understand the *meaning* of data, not just process it. Complex ontologies, RDF schemas, and reasoning engines promised a future where computers would truly comprehend information.

That vision seemed to fade as simpler, statistical approaches dominated the web.

**Until now.**

The Semantic Profiler revives the Semantic Web dream through modern AI. Instead of expecting humans to create semantic markup, we generate structured, meaningful profiling data that AI can directly understand and reason about. Claude doesn't just see numbers - it understands *what those numbers mean* for your application's performance.

## What It Does

- **Semantic Data Generation**: Creates structured, schema-validated performance profiles
- **AI-Native Analysis**: Designed for direct machine understanding and reasoning  
- **Automated Insights**: Transforms raw profiling data into actionable performance insights
- **Zero Manual Markup**: No RDF/XML nightmares - just clean, meaningful JSON

The future of performance analysis is semantic. And it's here.

## Usage

### Basic Usage
```bash
# Specify log directory - automatically uses the latest semantic profile
php server.php /tmp
```

### Development Environment Setup

For optimal profiling and semantic logging, use the provided PHP configuration:

```bash
# Generate semantic profiles with profiling
XDEBUG_MODE=trace php -c php-dev.ini demo/test-problematic-users.php

# Run Semantic Profiler MCP server with development settings
php -c php-dev.ini src/SemanticLog/server/bin/server.php /tmp

# Alternative: Use system PHP settings (if extensions already configured)
XDEBUG_MODE=trace php demo/test-problematic-users.php
```

### Complete Workflow

1. **Generate semantic profile with profiling**:
   ```bash
   cd /path/to/BEAR.Resource
   XDEBUG_MODE=trace php demo/test-problematic-users.php
   ```

2. **Start Claude CLI** and analyze (MCP server automatically uses latest profile):
   ```bash
   claude
   # Then use: getSemanticProfile or "パフォーマンス分析してください"
   # Or: semanticProfileAndAnalyze to run and analyze in one step
   ```

### PHP Configuration Notes

The `php-dev.ini` configuration:
- **Manual Profiling Control**: XHProf/Xdebug auto-start disabled
- **Measurement Scope**: Only measures resource processing (not composer/autoloader)
- **Performance**: Minimizes profiling overhead
- **Flexibility**: SemanticInvoker controls start/stop timing

This approach provides:
- Clean separation of application startup vs. resource processing
- Accurate bottleneck identification within business logic
- Reduced profiling overhead compared to full-script measurement

## Configuration

### MCP Server Setup

Add to project root `.mcp.json`:

```json
{
  "mcpServers": {
    "semantic-profiler": {
      "command": "php",
      "args": ["src/SemanticLog/server/bin/server.php", "/tmp"]
    }
  }
}
```

### Xdebug Configuration

The project includes `php-dev.ini` with optimized settings. Use it with `XDEBUG_MODE` environment variable:

```bash
# For tracing
XDEBUG_MODE=trace php -c php-dev.ini your-script.php

# For debugging + tracing
XDEBUG_MODE=debug,trace php -c php-dev.ini your-script.php
```

Key settings in `php-dev.ini`:
- `xdebug.compression_level=0` - Uncompressed .xt files
- `xdebug.start_with_request=no` - Manual control
- `xdebug.output_dir=/tmp` - Output directory

## Commands

### `getSemanticProfile`

Retrieves the latest structured semantic profiling data, ready for AI analysis and insight generation.

### `semanticProfileAndAnalyze`

Executes a PHP script with semantic profiling enabled and immediately provides AI-powered performance insights. The future of performance debugging in one command.

## Philosophy

Traditional profilers give you data. The Semantic Profiler gives you understanding.

This is what the Semantic Web was always meant to be - not a burden of manual markup, but machines naturally understanding the meaning embedded in structured data. Performance analysis that thinks.
