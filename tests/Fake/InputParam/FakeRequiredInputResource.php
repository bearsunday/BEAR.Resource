<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

use BEAR\Resource\Annotation\Input;

final class FakeRequiredInputResource
{
    public function onGet(#[Input] FakeSearchCriteria $criteria): void
    {
    }
}
