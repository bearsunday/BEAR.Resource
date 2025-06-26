<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam\Integration;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;

final class FakeNestedInputResourceIntegration extends ResourceObject
{
    public function onPost(#[Input] FakeUserWithAddressData $user): static
    {
        return $this;
    }
}
