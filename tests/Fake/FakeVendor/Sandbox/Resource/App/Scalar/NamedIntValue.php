<?php

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar;

use Ray\Di\Di\Named;

final class NamedIntValue
{
    public function __construct(
        public int $value,
        #[Named('other')] public ServiceInterface $service
    ){}
}
