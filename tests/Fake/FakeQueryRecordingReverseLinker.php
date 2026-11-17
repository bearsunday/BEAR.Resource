<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * Records the query each reverse link receives, and returns the URI unchanged
 */
final class FakeQueryRecordingReverseLinker implements ReverseLinkerInterface
{
    /** @var list<array<string, mixed>> */
    public array $queries = [];

    public function __invoke(string $uri, array $query): string
    {
        $this->queries[] = $query;

        return $uri;
    }
}
