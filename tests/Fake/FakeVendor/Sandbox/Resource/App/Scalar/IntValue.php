<?php

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar;

final class IntValue
{
    public function __construct(
        public int $value,
        public ServiceInterface $service
    ){}
}
