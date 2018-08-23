<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
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

    public function __construct(string $method, array $required, array $optional)
    {
        $this->method = $method;
        $this->required = $required;
        $this->optional = $optional;
    }
}
