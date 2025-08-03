# Xdebug Integration Analysis

## Implementation Summary

Successfully implemented Xdebug trace integration alongside the existing XHProf profiling in BEAR.Resource's semantic logging system. This provides AI debugging tools with both performance metrics (XHProf) and detailed execution flow (Xdebug traces).

## Key Components Implemented

### 1. Enhanced SemanticInvoker (`src/SemanticLog/SemanticInvoker.php`)

**XHProf Integration (existing):**
- Automatically profiles all resource invocations
- Saves profile data to `tests/tmp/xhprof/xhprof_{openId}_{uniqid}.xhprof`
- Links profile file path in semantic log context

**Xdebug Integration (new):**
- Conditionally starts Xdebug traces when extension is available
- Saves trace data to `tests/tmp/xdebug/trace_{uri}_{uniqid}.xt`
- Links trace file path in semantic log context
- Graceful fallback when Xdebug trace functionality is disabled

**Key Features:**
- Environment detection for development vs production
- Atomic profiling operations (both XHProf and Xdebug)
- Error handling prevents crashes when profiling fails
- File path sanitization for URIs containing special characters

### 2. XdebugTrace Utility Class (`src/SemanticLog/XdebugTrace.php`)

**Functionality:**
- `cleanup()` - Remove old trace files based on age
- `getRecentFiles()` - List recent trace files with metadata
- `readTrace()` - Read raw trace file content
- `getSummary()` - Parse trace data for analysis metrics

**Summary Metrics:**
- Total lines in trace
- Function call count
- Maximum call depth
- File size information
- Sample trace lines for quick inspection

### 3. Enhanced JSON Schemas

**Updated Schemas:**
- `complete-context.json` - Added `xdebug_trace_file` field
- `error-context.json` - Added `xdebug_trace_file` field

**Schema Compliance:**
- Optional fields (not required)
- Proper type definitions
- Descriptive field documentation

### 4. Comprehensive Testing (`tests/SemanticLogXdebugTest.php`)

**Test Coverage:**
- Schema validation for Xdebug fields
- XdebugTrace utility functionality
- Integration with resource invocation
- Graceful handling when Xdebug is unavailable

## Technical Architecture

### Tiered Profiling Approach

1. **Production Environment:**
   - XHProf only (5-15% overhead)
   - Semantic logs with performance data
   - Suitable for continuous monitoring

2. **Development Environment:**
   - XHProf + Xdebug traces (when enabled)
   - Detailed execution flow analysis
   - Function-level debugging information

### Data Flow

```
Resource Request
    ↓
SemanticInvoker.invoke()
    ↓
Start Profiling (XHProf + Xdebug)
    ↓
Execute Resource Method
    ↓
Stop Profiling & Save Files
    ↓
Link File Paths in Context
    ↓
Semantic Log Output
```

### File Organization

```
tests/tmp/
├── xhprof/
│   └── xhprof_{openId}_{uniqid}.xhprof
├── xdebug/
│   └── trace_{uri}_{uniqid}.xt
└── SemanticLoggerTest/
    └── {test}.json (with file path links)
```

## Environment Configuration

### For Full Xdebug Functionality

Add to `php.ini`:
```ini
xdebug.mode=trace
xdebug.trace_enable_trigger=1
xdebug.trace_output_dir=/tmp/xdebug
```

Or run with CLI flags:
```bash
php -dxdebug.mode=trace script.php
```

### Current Status

- Xdebug 3.4.4 is loaded but trace mode is disabled
- Implementation handles this gracefully with `@` error suppression
- XHProf integration works perfectly in current environment

## AI Debugging Benefits

### Combined Data Sources

1. **Semantic Logs** - Request/response structure and flow
2. **XHProf Data** - Performance bottlenecks and resource usage
3. **Xdebug Traces** - Detailed function call sequences

### AI Analysis Capabilities

With this integration, AI tools can:
- Correlate performance issues with specific code paths
- Trace execution flow for complex debugging scenarios
- Analyze memory usage patterns during resource invocation
- Identify inefficient code patterns through detailed profiling

### Example Context Output

```json
{
  "uri": "app://self/simple?id=demo",
  "method": "GET",
  "code": 200,
  "headers": {"Content-Type": "application/json"},
  "body": {"id": "demo", "message": "Hello from Simple"},
  "view": "{\"id\":\"demo\",\"message\":\"Hello from Simple\"}",
  "xhprof_file": "/path/to/xhprof_openId_unique.xhprof",
  "xdebug_trace_file": "/path/to/trace_app___self_simple_unique.xt"
}
```

## Performance Impact

### XHProf
- **Overhead:** 5-15% in production
- **Data Size:** 1-50KB per request
- **Use Case:** Continuous profiling

### Xdebug Traces
- **Overhead:** 50-300% (development only)
- **Data Size:** 10KB-10MB per request
- **Use Case:** Detailed debugging sessions

## Conclusion

The Xdebug integration successfully extends BEAR.Resource's semantic logging capabilities, providing AI tools with comprehensive debugging information. The implementation is production-ready with proper error handling and environment detection, while offering powerful development debugging capabilities when Xdebug trace mode is enabled.