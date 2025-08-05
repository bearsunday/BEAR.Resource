#!/usr/bin/env php
<?php

/**
 * Semantic Profiler MCP Server
 *
 * AI-powered performance analysis through structured semantic profiling.
 * Fulfilling the vision of Semantic Web - machines understanding meaning.
 */

declare(strict_types=1);

$logDirectory = $argv[1] ?? null;

if (! $logDirectory) {
    fwrite(STDERR, "Usage: php server.php <log-directory>\n");
    exit(1);
}

if (! is_dir($logDirectory)) {
    fwrite(STDERR, "Directory not found: $logDirectory\n");
    exit(1);
}

// Find the latest semantic log file
$pattern = rtrim($logDirectory, '/') . '/semantic-dev-*.json';
$files = glob($pattern);

if (empty($files)) {
    fwrite(STDERR, "No semantic log files found in directory: $logDirectory\n");
    exit(1);
}

// Sort by modification time, get the latest
usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
$logFile = $files[0];

fwrite(STDERR, "Using latest log file: $logFile\n");

// Make log directory available globally for runAndAnalyze function
$GLOBALS['logDirectory'] = $logDirectory;

// Handle JSON-RPC requests from STDIN
while ($line = fgets(STDIN)) {
    $request = json_decode(trim($line), true);

    if (! $request) {
        continue;
    }

    $response = match ($request['method'] ?? '') {
        'initialize' => [
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => [
                'protocolVersion' => '2024-11-05',
                'serverInfo' => ['name' => 'semantic-profiler', 'version' => '1.0.0'],
                'capabilities' => [
                    'tools' => (object) [],
                ],
            ],
        ],
        'tools/list' => [
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => [
                'tools' => [
                    [
                        'name' => 'getSemanticProfile',
                        'description' => 'Retrieve latest semantic performance profile - structured data designed for AI understanding and insight generation',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => (object) [],
                        ],
                    ],
                    [
                        'name' => 'semanticProfileAndAnalyze',
                        'description' => 'Execute PHP script with semantic profiling enabled, then automatically generate AI-powered performance insights and recommendations',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'script' => [
                                    'type' => 'string',
                                    'description' => 'Path to PHP script to execute',
                                ],
                                'xdebug_mode' => [
                                    'type' => 'string',
                                    'description' => 'Xdebug mode (default: trace)',
                                    'default' => 'trace',
                                ],
                            ],
                            'required' => ['script'],
                        ],
                    ],
                ],
            ],
        ],
        'tools/call' => [
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => handleToolCall($request['params'], $logFile),
        ],
        default => [
            'jsonrpc' => '2.0',
            'id' => $request['id'] ?? null,
            'error' => ['code' => -32601, 'message' => 'Method not found'],
        ]
    };

    fwrite(STDOUT, json_encode($response) . "\n");
}

function getLog(string $file): array
{
    $content = file_get_contents($file);

    return json_decode($content, true) ?: ['error' => 'Invalid log format'];
}

function handleToolCall(array $params, string $logFile): array
{
    $toolName = $params['name'] ?? '';

    if ($toolName === 'getSemanticProfile') {
        $logData = getLog($logFile);
        $analysisPrompt = getAnalysisPrompt();

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => $analysisPrompt . "\n\n```json\n" . json_encode($logData, JSON_PRETTY_PRINT) . "\n```",
                ],
            ],
            'isError' => false,
        ];
    }

    if ($toolName === 'semanticProfileAndAnalyze') {
        return semanticProfileAndAnalyze($params['arguments'] ?? []);
    }

    return [
        'content' => [
            [
                'type' => 'text',
                'text' => 'Unknown tool: ' . $toolName,
            ],
        ],
        'isError' => true,
    ];
}

function semanticProfileAndAnalyze(array $args): array
{
    $script = $args['script'] ?? '';
    $xdebugMode = $args['xdebug_mode'] ?? 'trace';

    if (! $script) {
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Error: script parameter is required',
                ],
            ],
            'isError' => true,
        ];
    }

    if (! file_exists($script)) {
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Error: Script not found: $script",
                ],
            ],
            'isError' => true,
        ];
    }

    // Record time before execution to find newly created log files
    $beforeExecution = time();

    // Execute the PHP script with profiling using php-dev.ini
    $env = "XDEBUG_MODE=$xdebugMode XDEBUG_CONFIG='compression_level=0'";
    $phpDevIni = __DIR__ . '/php-dev.ini';
    $command = "$env php -c " . escapeshellarg($phpDevIni) . ' ' . escapeshellarg($script) . ' 2>&1';

    $output = shell_exec($command);

    // Find semantic log files created during script execution
    $logDirectory = $GLOBALS['logDirectory'];
    $pattern = rtrim($logDirectory, '/') . '/semantic-dev-*.json';

    $files = glob($pattern);
    $newLogFiles = array_filter($files, static fn ($file) => filemtime($file) >= $beforeExecution);

    if (empty($newLogFiles)) {
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Script executed but no new semantic log generated.\nOutput:\n$output",
                ],
            ],
            'isError' => false,
        ];
    }

    // Get the newest log file from this execution
    usort($newLogFiles, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
    $executionLog = $newLogFiles[0];

    // Load and return the log data with analysis prompt
    $logData = getLog($executionLog);
    $analysisPrompt = getAnalysisPrompt();

    return [
        'content' => [
            [
                'type' => 'text',
                'text' => "Script executed successfully.\nLog file: $executionLog\n\n" . $analysisPrompt . "\n\n```json\n" . json_encode($logData, JSON_PRETTY_PRINT) . "\n```",
            ],
        ],
        'isError' => false,
    ];
}

/**
 * Generate analysis prompt for AI-native semantic web processing
 */
function getAnalysisPrompt(): string
{
    return <<<'PROMPT'
This is a BEAR.Resource application profiling log. Analyze YOUR APPLICATION CODE performance, not the framework itself.

Focus on business logic within resource methods and application-specific code. Ignore framework overhead and profiling overhead. 

If no performance issues are found in the application code, simply say "Application code performance is good."
PROMPT;
}
