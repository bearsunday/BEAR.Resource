<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class ResourceParam implements RequestParamInterface
{
    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag) $templated is configuration flag for URI template expansion
     * @psalm-pure
     */
    public function __construct(
        public string $uri = '',
        public string $param = '',
        public bool $templated = false,
    ) {
    }
}
