<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class ImportApp
{
    /** @var string */
    public $host;

    /** @var string */
    public $appName;

    /** @var string */
    public $context;

    public function __construct(string $host, string $appName, string $context)
    {
        $this->host = $host;
        $this->appName = $appName;
        $this->context = $context;
    }
}
