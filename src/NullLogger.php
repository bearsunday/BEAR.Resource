<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class NullLogger implements LoggerInterface
{
    public function __invoke(ResourceObject $ro): void
    {
    }
}
