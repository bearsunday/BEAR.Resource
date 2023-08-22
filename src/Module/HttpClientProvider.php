<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use Ray\Di\ProviderInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** @implements ProviderInterface<HttpClientInterface> */
final class HttpClientProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function get(): HttpClientInterface
    {
        return HttpClient::create();
    }
}
