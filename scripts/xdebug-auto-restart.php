<?php

declare(strict_types=1);

/**
 * Xdebug Auto-Restart Utility
 * 
 * Automatically detects and enables required Xdebug modes for tests by restarting
 * the current process with the proper XDEBUG_MODE environment variable.
 * 
 * Usage in your test bootstrap:
 * require_once __DIR__ . '/xdebug-auto-restart.php';
 * enable_xdebug(['trace']); // Enable trace mode
 * enable_xdebug(['debug', 'trace']); // Enable debug and trace modes  
 * enable_xdebug(['debug', 'trace', 'coverage']); // Enable multiple modes
 */

/**
 * Enable specific Xdebug modes for tests
 * 
 * This function checks if the required Xdebug modes are enabled and automatically
 * restarts the current process with the proper modes if they're not available.
 * 
 * @psalm-type XdebugMode = 'off'|'develop'|'coverage'|'debug'|'gcstats'|'profile'|'trace'
 * @param array<XdebugMode> $requiredModes The Xdebug modes to ensure are enabled.
 *                                         Common examples: ['trace'], ['debug', 'trace'], ['debug', 'trace', 'coverage']
 * @return void
 * @phpstan-param array<string> $requiredModes
 */
function enable_xdebug(array $requiredModes): void
{
    if (!extension_loaded('xdebug') || getenv('XDEBUG_RESTART_ATTEMPTED') !== false) {
        return;
    }
    
    $currentMode = ini_get('xdebug.mode');
    
    // Handle case where ini_get returns false
    if ($currentMode === false) {
        $currentMode = '';
    }
    
    // Parse current modes
    $currentModes = array_filter(array_map('trim', explode(',', $currentMode)));
    
    // Check if all required modes are already enabled
    $missingModes = array_diff($requiredModes, $currentModes);
    if (empty($missingModes)) {
        return; // All required modes already enabled
    }
    
    // Merge current modes with required modes
    $finalModes = array_unique(array_merge($currentModes, $requiredModes));
    $finalModeString = implode(',', $finalModes);
    
    error_log("Xdebug auto-restart: enabling modes " . implode(',', $missingModes) . " (was: " . implode(',', $currentModes) . ")");
    
    // Set flag to prevent infinite recursion
    putenv('XDEBUG_RESTART_ATTEMPTED=1');
    
    // Restart the same command with required modes enabled
    $argv = $_SERVER['argv'] ?? [];
    $cmd = "XDEBUG_MODE=$finalModeString " . implode(' ', array_map('escapeshellarg', $argv));
    
    // Execute and pass through exit code
    passthru($cmd, $exitCode);
    exit($exitCode);
}