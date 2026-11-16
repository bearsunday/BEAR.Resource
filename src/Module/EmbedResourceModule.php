<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AbstractEmbedPage;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\EmbedInterceptor;
use BEAR\Resource\EmbedInterceptorInterface;
use BEAR\Resource\EmbedPageInterceptor;
use BEAR\Resource\EmbedPageInterceptorInterface;
use Override;
use Ray\Di\AbstractModule;

/**
 * Provides EmbedInterceptor interceptor bindings
 */
final class EmbedResourceModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(EmbedPageInterceptorInterface::class)->to(EmbedPageInterceptor::class);
        $this->bindInterceptor(
            $this->matcher->annotatedWith(AbstractEmbedPage::class),
            $this->matcher->startsWith('onGet'),
            [EmbedPageInterceptorInterface::class],
        );
        $this->bind(EmbedInterceptorInterface::class)->to(EmbedInterceptor::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Embed::class),
            [EmbedInterceptorInterface::class],
        );
    }
}
