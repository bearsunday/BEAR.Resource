<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AbstractResourceLayout;
use BEAR\Resource\ResourceLayoutInterceptor;
use BEAR\Resource\ResourceLayoutInterceptorInterface;
use Override;
use Ray\Di\AbstractModule;

final class ResourceLayoutModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(ResourceLayoutInterceptorInterface::class)->to(ResourceLayoutInterceptor::class);
        $this->bindInterceptor(
            $this->matcher->annotatedWith(AbstractResourceLayout::class),
            $this->matcher->startsWith('onGet'),
            [ResourceLayoutInterceptorInterface::class],
        );
    }
}
