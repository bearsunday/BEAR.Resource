<?php

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar;

use BEAR\Resource\Annotation\FakeLog;
use Ray\Di\Di\Named;

final class NamedIntValue
{
    public function __construct(
        public int $value,
        #[FakeLog, Named('other')] public ServiceInterface $service
    ){}
}
