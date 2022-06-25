<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Ray\Di\Di\Qualifier;

// phpcs:enable

/**
 * @Annotation
 * @Target("METHOD")
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER), Qualifier]
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
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(array $values = [], string $uri = '', string $param = '', bool $templated = false)
    {
        $this->uri = $values['uri'] ?? $uri;
        $this->param = $values['param'] ?? $param;
        $this->templated = $values['templated'] ?? $templated;
    }
}
