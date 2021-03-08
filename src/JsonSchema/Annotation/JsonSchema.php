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
    /**
     * Json schema body key name
     *
     * @var string
     */
    public $key = '';

    /** @var string */
    public $schema;

    /**
     * Input parameter validation schema
     *
     * @var string
     */
    public $params;

    public function __construct(string $schema = '', string $key = '', string $params = '')
    {
        $this->key = $key;
        $this->schema = $schema;
        $this->params = $params;
    }
}
