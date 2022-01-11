<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class Params
{
    public string $method;

    /** @var string[] */
    public array $required = [];

    /** @var string[] */
    public array $optional = [];

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
