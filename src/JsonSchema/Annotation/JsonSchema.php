<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @Target("METHOD")
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class JsonSchema
{
    public function __construct(
        public string $schema = '',
        /**
         * Json schema body key name
         */
        public string $key = '',
        /**
         * Input parameter validation schema
         */
        public string $params = '',
        /**
         * @Enum({"view", "body"})
         */
        public string $target = 'body'
    )
    {
    }
}
