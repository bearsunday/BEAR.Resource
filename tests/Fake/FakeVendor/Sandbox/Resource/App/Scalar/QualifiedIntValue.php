<?php

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar;

final class QualifiedIntValue
{
    public function __construct(
        public int $value,
        #[OtherQualifier] public ServiceInterface $service
    ){}
}
