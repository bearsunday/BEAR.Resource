<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\ResourceObject;
use RuntimeException;

final class Error extends ResourceObject
{
    public function onGet(string $type = 'runtime'): self
    {
        switch ($type) {
            case 'runtime':
                throw new RuntimeException('This is a test runtime exception');
            case 'invalid_argument':
                throw new \InvalidArgumentException('Invalid argument provided');
            case 'domain':
                throw new \DomainException('Domain logic error occurred');
            default:
                throw new RuntimeException('Unknown error type: ' . $type);
        }
    }
}