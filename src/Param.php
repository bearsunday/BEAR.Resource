<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

final class Param
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $param;

    /**
     * @param string $class
     * @param string $method
     * @param string $param
     */
    public function __construct($class, $method, $param)
    {
        $this->class = $class;
        $this->method = $method;
        $this->param  = $param;
    }
}
