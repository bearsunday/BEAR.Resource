<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

/**
 * Input parameter entity attribute
 *
 * This attribute marks parameters that should be automatically
 * constructed from HTTP input parameters as domain objects.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Input
{
    public function __construct(
        /**
         * Key for structured data (like ClassParam behavior)
         *
         * When specified, looks for data under this key:
         * #[Input(key: 'user')] -> expects $query['user'] = ['name' => 'John', 'age' => 30]
         *
         * When null, uses flat parameter mapping:
         * #[Input] -> expects $query = ['firstName' => 'John', 'lastName' => 'Doe']
         */
        public readonly string|null $key = null,
    ) {
    }
}
