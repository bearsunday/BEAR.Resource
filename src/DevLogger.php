<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;

use function in_array;
use function sprintf;

final class DevLogger implements LoggerInterface
{
    public function __construct(
        private PsrLoggerInterface $logger,
    ) {
    }

    public function __invoke(ResourceObject $ro): void
    {
        $unsafeMethod = ['post', 'put', 'patch', 'delete'];
        $level = in_array($ro->uri->method, $unsafeMethod, true) ? LogLevel::INFO : LogLevel::DEBUG;
        $this->logger->log($level, sprintf('request: %s %s', $ro->uri->method, (string) $ro->uri));
        $this->logger->log($level, sprintf('response: %s %s', $ro->code, (string) $ro));
    }
}
