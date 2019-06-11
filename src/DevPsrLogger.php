<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;
use function sprintf;

final class DevPsrLogger implements LoggerInterface
{
    /**
     * @var PsrLoggerInterface
     */
    private $logger;

    public function __construct(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(ResourceObject $ro) : void
    {
        $msg = sprintf('request: %s %s', $ro->uri->method, (string) $ro->uri);
        $level = ($ro->uri->method === 'get') ? LogLevel::DEBUG : LogLevel::INFO;
        $this->logger->log($level, $msg);
        $this->logger->log($level, sprintf('response %s', $ro->code), json_decode((string) clone $ro, true));
    }
}
