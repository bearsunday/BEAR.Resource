<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Override;
use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

/**
 * Matches a method that declares #[Link(rel: 'prefetch')]
 *
 * @internal
 */
final class LinkHeaderMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     *
     * #[Link] targets methods only, so no class matches.
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        foreach ($method->getAttributes(Link::class) as $attribute) {
            if ($attribute->newInstance()->rel === 'prefetch') {
                return true;
            }
        }

        return false;
    }
}
