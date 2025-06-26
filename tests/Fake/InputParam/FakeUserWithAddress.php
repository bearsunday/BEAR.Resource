<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

use BEAR\Resource\Annotation\Input;

final class FakeUserWithAddress
{
    public function __construct(
        public readonly string               $name,
        public readonly int                  $age,
        #[Input] public readonly FakeAddress $address,
    ) {
    }
}
