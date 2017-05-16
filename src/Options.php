<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
