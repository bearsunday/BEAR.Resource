<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
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
        $this->logger->debug($msg);
        $this->logger->debug(sprintf('response %s', $ro->code), json_decode((string) clone $ro, true));
    }
}
