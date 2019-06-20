<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function error_log;
use function sprintf;

final class ErrorLogLogger implements LoggerInterface
{
    public function __invoke(ResourceObject $ro) : void
    {
        $requestLog = sprintf('request: %s %s', $ro->uri->method, (string) $ro->uri);
        $responseLog = sprintf('response %s %s', $ro->code, $ro->view);
        error_log($requestLog);
        error_log($responseLog);
    }
}
