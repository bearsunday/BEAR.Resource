<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

final class Params
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var string[]
     */
    public $required = [];

    /**
     * @var string[]
     */
    public $optional = [];

    public function __construct($method, array $required, array $optional)
    {
        $this->method = $method;
        $this->required = $required;
        $this->optional = $optional;
    }
}
