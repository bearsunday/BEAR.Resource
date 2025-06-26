<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

final class FakeAddress
{
    /**
     * @param string $city   City name
     * @param string $street Street address
     */
    public function __construct(
        public readonly string $city,
        public readonly string $street,
    ) {
    }
}
