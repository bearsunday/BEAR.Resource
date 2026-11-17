<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Override;
use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

use function in_array;

/**
 * Matches a method that declares a #[Link] whose rel is in LinkHeaderInterceptor::RELS
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

    /** {@inheritDoc} */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        foreach ($method->getAttributes(Link::class) as $attribute) {
            $linkArguments = $attribute->getArguments();
            if (in_array($linkArguments['rel'] ?? $linkArguments[0] ?? null, LinkHeaderInterceptor::RELS, true)) {
                return true;
            }
        }

        return false;
    }
}
