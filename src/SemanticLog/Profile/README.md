# BEAR.Resource Profile System

Unified profiling API integrating XHProf, Xdebug, and PHP profiling.

## Overview

```json
{
  "profile": {
    "xhprof": {"file": "/path/to/xhprof.xhprof"}, 
    "xdebug": {"file": "/path/to/trace.xt.gz"},
    "php": {"backtrace": [...]}
  }
}
```

## Components

### Profile Class
Main aggregation class combining three profiling types:

- **XHProfResult**: XHProf profiling data
- **XdebugTrace**: Xdebug trace data  
- **PhpProfile**: PHP backtrace (always available)

### Availability

| Profiler | Requirements | Always Available |
|----------|-------------|------------------|
| **XHProf** | `ext-xhprof` | ❌ |
| **Xdebug** | `ext-xdebug` + trace mode | ❌ |
| **PHP** | None | ✅ |

## Configuration

### XHProf
No configuration needed - starts/stops dynamically.

### Xdebug

**Basic setup:**
```ini
# php.ini
xdebug.mode=trace

# Or environment variable (recommended)
XDEBUG_MODE=trace
```

**Optional settings:**
```ini
xdebug.output_dir=/var/tmp
xdebug.start_with_request=yes
xdebug.compression_level=1
```

### Usage

**Environment control:**
```bash
# Enable tracing
XDEBUG_MODE=trace php script.php

# Disable profiling
XDEBUG_MODE=off php script.php
```

**Development:**
```bash
XDEBUG_MODE=trace ./vendor/bin/phpunit
```

**Production:**
```bash
XDEBUG_MODE=off php script.php
```

## Troubleshooting

**Empty results (`"xdebug": []`):**

1. Check Xdebug installation: `php -m | grep xdebug`
2. Check trace mode: `php -r "echo getenv('XDEBUG_MODE') ?: ini_get('xdebug.mode');"`
3. Check output directory permissions

## API Reference

```php
// Profile aggregation
final class Profile implements JsonSerializable
{
    public function __construct(
        public ?XHProfResult $xhprof = null,
        public ?XdebugTrace $xdebug = null, 
        public ?PhpProfile $php = null,
    );
}

// XHProf profiling
final class XHProfResult implements JsonSerializable
{
    public static function start(): self;
    public function stop(string $uri): self;
}

// Xdebug trace
final class XdebugTrace implements JsonSerializable
{
    public static function start(): self;
    public function stop(): self;
}

// PHP backtrace
final class PhpProfile implements JsonSerializable  
{
    public static function capture(int $backtraceLimit = 10): self;
}
```

## Usage Example

```php
// Start profiling
$xhprofResult = XHProfResult::start();
$xdebugTrace = XdebugTrace::start(); 
$phpProfile = PhpProfile::capture();

$profile = new Profile(
    xhprof: $xhprofResult,
    xdebug: $xdebugTrace,
    php: $phpProfile,
);

// Stop profiling
$xhprofResult = $profile->xhprof?->stop($uri);
$xdebugTrace = $profile->xdebug?->stop();
$phpProfile = PhpProfile::capture();

$profile = new Profile(
    xhprof: $xhprofResult,
    xdebug: $xdebugTrace, 
    php: $phpProfile,
);
```

## Performance

- **PHP Profile**: Minimal overhead (~1ms)
- **XHProf**: Low overhead (few ms)  
- **Xdebug**: High overhead (10-100ms, depends on trace size)

**Production recommendation**: XHProf only or PHP Profile only
**Development recommendation**: All profilers enabled