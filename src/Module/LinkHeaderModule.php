<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\LinkHeaderInterceptor;
use BEAR\Resource\LinkHeaderMatcher;
use BEAR\Resource\ResourceObject;
use Override;
use Ray\Di\AbstractModule;

/**
 * Outputs the #[Link] links of onGet() whose rel is in LinkHeaderInterceptor::RELS as a Link header
 *
 * @internal
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
