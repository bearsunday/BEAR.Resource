<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/** @psalm-immutable */
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
     * @param array{uri?: string, param?: string, templated?: bool} $values
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag) $templated is configuration flag for URI template expansion
     *
     * @psalm-capabilities read-props
     */
    public function __construct(array $values = [], string $uri = '', string $param = '', bool $templated = false)
    {
        $this->uri = $values['uri'] ?? $uri;
        $this->param = $values['param'] ?? $param;
        $this->templated = $values['templated'] ?? $templated;
    }
}
