<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\Annotation\Input;

final class NestedInputTestObject
{
    public function __construct(
        public string $title,
        #[Input] public SimpleTestObject $nested,
    ) {}
}