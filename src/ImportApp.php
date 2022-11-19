<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class ImportApp
{
    public function __construct(public string $host, public string $appName, public string $context)
    {
    }
}
