<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class Options
{
    /**
     * @var array<int, string>
     */
    public $allow = [];

    /**
     * @var array<Params>
     */
    public $params = [];

    /**
     * @param array<int, string> $allow
     * @param array<Params>      $params
     */
    public function __construct(array $allow, array $params)
    {
        $this->allow = $allow;
        $this->params = $params;
    }
}
