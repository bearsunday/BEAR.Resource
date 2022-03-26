<?php

declare(strict_types=1);

namespace BEAR\Resource\Mock;

class PersonConstructor
{
    private $name;
    private $age;

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function __construct(int $age, string $name)
    {
        $this->name = $name;
        $this->age = $age;
    }
}
