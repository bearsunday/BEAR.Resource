<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Types;

/**
 * @psalm-import-type ConstraintName from Types
 * @psalm-immutable
 */
final readonly class ConstraintViolation
{
    /**
     * @param ConstraintName|string $name   Constraint keyword (typed as literal-union for known JSON Schema keywords; runtime stays string for forward compatibility)
     * @param array<string, mixed>  $params
     *
     * @psalm-pure
     */
    public function __construct(
        public string $name,
        public array $params,
    ) {
    }
}
