<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class Options
{
    /**
     * @param list<string>  $allow
     * @param array<Params> $params
     */
    public function __construct(
        public array $allow,
        public array $params,
    ) {
    }
}
