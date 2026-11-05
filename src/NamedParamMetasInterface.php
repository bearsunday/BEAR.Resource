<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface NamedParamMetasInterface
{
    /**
     * @return array<string, ParamInterface>
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    public function __invoke(callable $callable): array;
}
