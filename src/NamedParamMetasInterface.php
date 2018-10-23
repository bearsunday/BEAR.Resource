<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface NamedParamMetasInterface
{
    public function __invoke(callable $callable) : array;
}
