<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\LinkHeaderInterceptor;
use BEAR\Resource\LinkHeaderMatcher;
use BEAR\Resource\ResourceObject;
use Override;
use Ray\Di\AbstractModule;

/**
 * Outputs the #[Link(rel: 'prefetch')] links of onGet() as a Link header
 */
final class LinkHeaderModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(LinkHeaderInterceptor::class);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->logicalAnd(
                $this->matcher->startsWith('onGet'),
                new LinkHeaderMatcher(),
            ),
            [LinkHeaderInterceptor::class],
        );
    }
}
