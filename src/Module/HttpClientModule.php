<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\HttpRequestCurl;
use Ray\Di\AbstractModule;

/**
 * Provides HttpClientInterface bindings
 */
final class HttpClientModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(HttpRequestCurl::class);
    }
}
