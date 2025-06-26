<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam\Integration;

final class FakeAddressData
{
    /**
     * @param string $city    City name
     * @param string $zipCode ZIP code
     */
    public function __construct(
        public readonly string $city,
        public readonly string $zipCode,
    ) {
    }
}
