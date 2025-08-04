<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\LogJson;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Ray\Di\Di\Named;
use Throwable;

use function date;
use function file_put_contents;
use function getmypid;
use function json_encode;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;

use const JSON_PRETTY_PRINT;
use const LOCK_EX;

/**
 * Development log persister for MCP server integration
 *
 * Handles file persistence of semantic logs with Profile data for AI-assisted debugging.
 * Uses single responsibility principle - only concerned with log persistence.
 */
final class DevLogPersister
{
    public function __construct(
        #[Named('dev_log_directory')]
        private string $logDirectory = '',
    ) {
        // Use system temp directory if no directory specified
        if ($this->logDirectory !== '') {
            return;
        }

        $this->logDirectory = sys_get_temp_dir();
    }

    /**
     * Persist semantic logs to file for development/debugging purposes
     */
    public function persistLogs(SemanticLoggerInterface $logger): void
    {
        try {
            $logData = $logger->flush();
            $this->saveToFile($logData);
        } catch (Throwable) {
            // Silent failure - don't break main processing
            // In development, log persistence should never affect application flow
        }
    }

    private function saveToFile(LogJson $logData): void
    {
        $jsonContent = json_encode($logData, JSON_PRETTY_PRINT);
        if ($jsonContent === false) {
            return; // Skip if JSON encoding fails
        }

        $filename = $this->generateFilename();
        file_put_contents($filename, $jsonContent, LOCK_EX);
    }

    /**
     * Generate unique filename for concurrent request safety
     */
    private function generateFilename(): string
    {
        $timestamp = date('Y-m-d_H-i-s-u'); // Microseconds for concurrency
        $processId = getmypid(); // Process ID for true uniqueness
        $uniqueId = uniqid();

        return sprintf(
            '%s/semantic-dev-%s-%s-%s.json',
            $this->logDirectory,
            $timestamp,
            $processId !== false ? $processId : 'unknown',
            $uniqueId,
        );
    }
}
