<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\ResourceObject;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\Di\Named;
use Throwable;

use function date;
use function file_put_contents;
use function json_encode;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;

use const JSON_PRETTY_PRINT;

/**
 * SemanticInvoker with automatic log persistence on close
 *
 * Extends the standard SemanticInvoker to automatically write log files
 * immediately after each operation completes (either successfully or with error).
 * This ensures logs are persisted without relying on shutdown functions.
 */
final class AutoPersistSemanticInvoker implements InvokerInterface
{
    public function __construct(
        #[Named('original')]
        private InvokerInterface $invoker,
        private SemanticLoggerInterface $logger,
        private ContextFactoryInterface $factory,
        #[Named('log_directory')]
        private string $logDirectory = '',
    ) {
        // Use system temp directory if no directory specified
        if ($this->logDirectory !== '') {
            return;
        }

        $this->logDirectory = sys_get_temp_dir();
    }

    #[Override]
    public function invoke(AbstractRequest $request): ResourceObject
    {
        $openContext = $this->factory->createOpenContext($request);
        $openId = $this->logger->open($openContext);

        try {
            $result = $this->invoker->invoke($request);
            $closeContext = $this->factory->createCompleteContext($result, $openContext);
            $this->logger->close($closeContext, $openId);

            // Immediately persist the completed log
            $this->persistLog('complete');

            return $result;
        } catch (Throwable $e) {
            $errorContext = $this->factory->createErrorContext($e);
            $this->logger->close($errorContext, $openId);

            // Immediately persist the error log
            $this->persistLog('error');

            throw $e;
        }
    }

    private function persistLog(string $type): void
    {
        $logJson = $this->logger->flush();
        $jsonContent = json_encode($logJson, JSON_PRETTY_PRINT);
        if ($jsonContent === false) {
            return; // Skip if JSON encoding fails
        }

        $timestamp = date('Y-m-d_H-i-s');
        $uniqueId = uniqid();
        $filename = sprintf(
            '%s/semantic-log_%s_%s_%s.json',
            $this->logDirectory,
            $type,
            $timestamp,
            $uniqueId,
        );

        file_put_contents($filename, $jsonContent);
    }
}
