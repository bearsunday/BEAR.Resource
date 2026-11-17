<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;
use Stringable;

use function http_build_query;
use function is_array;

/** @psalm-import-type Query from Types */
abstract class AbstractUri implements Stringable
{
    /** @var string */
    public $scheme;

    /** @var string */
    public $host;

    /** @var string */
    public $path;

    /**
     * Associative query array
     *
     * @var Query
     */
    public $query = [];

    /** @var string */
    public $method = 'get';

    /**
     * @return string
     *
     * @psalm-taint-specialize
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function __toString()
    {
        return "{$this->scheme}://{$this->host}{$this->path}" . ($this->query ? '?' . http_build_query($this->stringValues($this->query)) : '');
    }

    /**
     * http_build_query() writes an object as its public properties, which a Stringable value object usually has none of
     *
     * @param array<array-key, mixed> $query
     *
     * @return array<array-key, mixed>
     *
     * @psalm-capabilities read-props
     */
    private function stringValues(array $query): array
    {
        /** @var mixed $value */
        foreach ($query as $key => $value) {
            if ($value instanceof Stringable) {
                /** @psalm-suppress ImpureMethodCall A query value object's __toString() reads its own value */
                $query[$key] = (string) $value;
            } elseif (is_array($value)) {
                $query[$key] = $this->stringValues($value);
            }
        }

        return $query;
    }
}
