<?php

declare(strict_types=1);

namespace BEAR\Resource\Mock;

class PersonConstructor
{
    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function __construct(private int $age, private string $name)
    {
    }
}
