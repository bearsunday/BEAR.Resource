<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

use BEAR\Resource\Annotation\Input;

final class FakeNestedInputResource
{
    public function onPost(#[Input] FakeUserWithAddress $user): void
    {
    }
}
