<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use Attribute;
// phpcs:enable

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute]
final class ResourceParam implements RequestParamInterface
{
    /** @var string */
    public $param;

    /** @var string */
    public $uri;

    /** @var bool */
    public $templated;

    /** @param array{uri?: string, param?: string, templated?: bool} $values */
    public function __construct(array $values = [], string $uri = '', string $param = '', bool $templated = false)
    {
        $this->uri = $values['uri'] ?? $uri;
        $this->param = $values['param'] ?? $param;
        $this->templated = $values['templated'] ?? $templated;
    }
}
