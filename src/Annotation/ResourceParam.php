<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class ResourceParam implements RequestParamInterface
{
    /** @var string */
    public $param;

    /** @var string */
    public $uri;

    /** @var bool */
    public $templated;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag) $templated is configuration flag for URI template expansion
     * @psalm-pure
     */
    public function __construct(string $uri = '', string $param = '', bool $templated = false)
    {
        $this->uri = $uri;
        $this->param = $param;
        $this->templated = $templated;
    }
}
