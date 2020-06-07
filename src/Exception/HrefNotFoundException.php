<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use RuntimeException;

/**
 * Href not found in (HAL renderer) exception
 */
class HrefNotFoundException extends RuntimeException implements ExceptionInterface
{
}
