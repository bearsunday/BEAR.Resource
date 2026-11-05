<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\HttpRequestCurl;
use BEAR\Resource\HttpRequestHeaders;
use BEAR\Resource\HttpRequestInterface;
use BEAR\Resource\HttpResourceObject;
use Override;
use Ray\Di\AbstractModule;

/**
 * Provides HttpRequestCurl bindings
 */
final class HttpClientModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(HttpRequestInterface::class)->to(HttpRequestCurl::class);
        $this->bind(HttpRequestHeaders::class);
        $this->bind(HttpResourceObject::class);
    }
}
