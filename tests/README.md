# Test Utilities

## Xdebug Auto-Restart Utility

The `scripts/xdebug-auto-restart.php` utility automatically enables Xdebug trace mode for tests that require it.

### Usage in BEAR.Resource Project

The utility is **enabled by default** in this project's test bootstrap. It automatically detects when Xdebug trace mode is needed and restarts the process with the proper configuration.

#### Disabling Auto-Restart (if needed)

To disable the auto-restart functionality, comment out these lines in `tests/bootstrap.php`:

```php
<?php
// Load Xdebug auto-restart utility
// require_once dirname(__DIR__) . '/scripts/xdebug-auto-restart.php';
// enable_xdebug(['trace']);
```

### Usage in Other Projects

#### Basic Usage

In your test bootstrap file:

```php
<?php
require_once 'vendor/bear/resource/scripts/xdebug-auto-restart.php';

// Enable trace mode only
enable_xdebug(['trace']);

// Enable debug and trace modes
enable_xdebug(['debug', 'trace']);

// Enable multiple modes for comprehensive profiling
enable_xdebug(['debug', 'trace', 'coverage']);
```

#### Common Usage Patterns

```php
<?php
// For semantic logging with profiling
enable_xdebug(['trace']);

// For debugging with profiling
enable_xdebug(['debug', 'trace']);

// For full development environment  
enable_xdebug(['debug', 'trace', 'coverage']);
```

### How It Works

1. **Detection**: Checks if Xdebug is loaded and required modes are enabled
2. **Auto-restart**: If modes are missing, restarts the current process with proper `XDEBUG_MODE` environment variable
3. **Recursion protection**: Uses `XDEBUG_RESTART_ATTEMPTED` environment variable to prevent infinite loops

### Example Output

```
Auto-enabling Xdebug trace for all tests
Current modes: debug
Final modes: debug,trace
Restarting with trace enabled...

PHPUnit 9.6.23 by Sebastian Bergmann and contributors.
...
```

### Benefits

- **Zero configuration**: Works automatically without manual environment setup
- **Performance**: Only restarts when necessary
- **Compatibility**: Preserves existing Xdebug modes (OR operation, not replacement)
- **Flexibility**: Can be used in any PHP test framework, not just PHPUnit

### Use Cases

- **Semantic logging with profiling**: When tests need Xdebug trace files for performance analysis
- **Coverage reports**: When combining multiple Xdebug modes
- **Development environments**: Where Xdebug configuration varies between developers