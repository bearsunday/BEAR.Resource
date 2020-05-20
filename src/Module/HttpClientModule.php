<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(HttpClientInterface::class)->toProvider(HttpClientProvider::class);
    }
}
