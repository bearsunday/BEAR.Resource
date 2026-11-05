<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface NamedParamMetasInterface
{
    /** @return array<string, ParamInterface> */
    public function __invoke(callable $callable): array;
}
