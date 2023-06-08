<?php

namespace BEAR\Resource;

final class FakeReverseLinker implements ReverseLinkerInterface
{
    public function __invoke(string $uri, array $query): string
    {
        return "/user/{$query['id']}";
    }
}
