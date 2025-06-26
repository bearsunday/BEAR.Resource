<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

use BEAR\Resource\Annotation\Input;

final class FakeSingleInputResource
{
    /** @param FakeUser $user User information */
    public function onGet(#[Input] FakeUser $user): void
    {
    }
}
