<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface LoggerInterface
{
    /** @psalm-impure */
    public function __invoke(ResourceObject $ro): void;
}
