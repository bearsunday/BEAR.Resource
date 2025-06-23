<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar;

class OtherServiceImplementation implements ServiceInterface
{
    public function serve(): string
    {
        return 'other service implementation';
    }
}
