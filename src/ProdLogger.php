<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

use function in_array;
use function sprintf;

final class ProdLogger implements LoggerInterface
{
    public function __construct(private PsrLoggerInterface $logger)
    {
    }

    public function __invoke(ResourceObject $ro): void
    {
        $unsafeMethod = ['post', 'put', 'patch', 'delete'];
        if (! in_array($ro->uri->method, $unsafeMethod, true)) {
            return;
        }

        $msg = sprintf('%s %s %s', $ro->code, $ro->uri->method, (string) $ro->uri);
        $this->logger->info($msg);
    }
}
