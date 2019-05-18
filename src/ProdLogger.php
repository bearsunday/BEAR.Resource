<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function error_log;
use function in_array;
use function sprintf;

final class ProdLogger implements LoggerInterface
{
    public function __invoke(ResourceObject $ro) : void
    {
        $method = $ro->uri->method;
        $unsafeMethod = ['put', 'post', 'delete'];
        if (! in_array($method, $unsafeMethod, true)) {
            return;
        }
        $msg = sprintf('%s %s %s', $ro->code, $ro->uri->method, (string) $ro->uri);
        error_log($msg);
    }
}
