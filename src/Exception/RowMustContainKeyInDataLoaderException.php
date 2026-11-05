<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use RuntimeException;

/**
 * Thrown when DataLoader returns rows that don't contain the required key column
 */
final class RowMustContainKeyInDataLoaderException extends RuntimeException implements ExceptionInterface
{
    /** @psalm-pure */
    public function __construct(string $key)
    {
        parent::__construct($key);
    }
}
