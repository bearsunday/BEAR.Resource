<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

use BEAR\Resource\Annotation\Input;

final class FakeInputWithDefaultsResource
{
    public function onGet(#[Input] FakePager $pager): void
    {
    }
}
