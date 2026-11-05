<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\EmbedInterceptor;
use BEAR\Resource\EmbedInterceptorInterface;
use Override;
use Ray\Di\AbstractModule;

/**
 * Provides EmbedInterceptor interceptor bindings
 */
final class EmbedResourceModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(EmbedInterceptorInterface::class)->to(EmbedInterceptor::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Embed::class),
            [EmbedInterceptorInterface::class],
        );
    }
}
