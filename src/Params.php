<?php

declare(strict_types=1);

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

    /**
     * @param list<string> $required
     * @param list<string> $optional
     */
    public function __construct(string $method, array $required, array $optional)
    {
        $this->method = $method;
        $this->required = $required;
        $this->optional = $optional;
    }
}
