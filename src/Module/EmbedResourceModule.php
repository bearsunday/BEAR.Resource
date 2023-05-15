<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\EmbedInterceptor;
use Ray\Di\AbstractModule;

/**
 * Provides EmbedInterceptor interceptor bindings
 */
final class EmbedResourceModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Embed::class),
            [EmbedInterceptor::class],
        );
    }
}
