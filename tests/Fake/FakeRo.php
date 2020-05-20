<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class FakeRo
{
    public function __invoke(ResourceObject $ro) : ResourceObject
    {
        $classPath = (new \ReflectionClass($ro))->getName();
        $mockPath = strtolower(str_replace('\\', '/', $classPath));
        $ro->uri = new Uri(sprintf('app://self/%s', $mockPath));

        return $ro;
    }
}
