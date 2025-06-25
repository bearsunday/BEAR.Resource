<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\Annotation\Input;

final class NestedCamelCaseUser
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        #[Input] public readonly NestedCamelCaseAddress $homeAddress,
    ) {
    }
}