<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class ImportApp
{
    /**
     * Host name
     *
     * @var string
     */
    public $host;

    /**
     * App name
     *
     * @var string
     */
    public $appName;

    /**
     * Context
     *
     * @var string
     */
    public $context;

    /**
     * @param string $host
     * @param string $appName
     * @param string $context
     */
    public function __construct($host, $appName, $context)
    {
        $this->host = $host;
        $this->appName = $appName;
        $this->context = $context;
    }
}
