<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class DevZeroLogger implements LoggerInterface
{
    public function __invoke(ResourceObject $ro) : void
    {
        $msg = sprintf('%s %s %s', $ro->code, $ro->uri->method, (string) $ro->uri);
        error_log($msg);
    }
}
