<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam\Integration;

use BEAR\Resource\Annotation\Input;

final class FakeUserWithAddressData
{
    public function __construct(
        public readonly string                   $firstName,
        public readonly string                   $lastName,
        #[Input] public readonly FakeAddressData $address,
    ) {
    }
}
