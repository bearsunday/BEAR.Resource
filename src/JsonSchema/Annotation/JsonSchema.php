<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

/** @codeCoverageIgnore */
#[Attribute(Attribute::TARGET_METHOD)]
final class JsonSchema
{
    /** @psalm-pure */
    public function __construct(
        public string $schema = '',
        /** Json schema body key name */
        public string $key = '',
        /** Input parameter validation schema */
        public string $params = '',
        public string $target = 'body',
    ) {
    }
}
