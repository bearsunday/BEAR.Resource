<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\PrefetchLinkHeaderInterceptor;
use BEAR\Resource\ResourceObject;
use Override;
use Ray\Di\AbstractModule;

/**
 * Outputs the #[Link(rel: 'prefetch')] links of onGet() as a Link header
 */
final class PrefetchLinkHeaderModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(PrefetchLinkHeaderInterceptor::class);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->logicalAnd(
                $this->matcher->startsWith('onGet'),
                $this->matcher->annotatedWith(Link::class),
            ),
            [PrefetchLinkHeaderInterceptor::class],
        );
    }
}
