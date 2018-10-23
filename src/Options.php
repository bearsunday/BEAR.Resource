<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class Options
{
    /**
     * @var array
     */
    public $allow = [];

    /**
     * @var Params[]
     */
    public $params = [];

    public function __construct(array $allow, array $params)
    {
        $this->allow = $allow;
        $this->params = $params;
    }
}
