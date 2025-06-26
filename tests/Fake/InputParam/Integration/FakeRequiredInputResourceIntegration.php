<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam\Integration;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;

final class FakeRequiredInputResourceIntegration extends ResourceObject
{
    public function onGet(#[Input] FakeSearchData $criteria): static
    {
        return $this;
    }
}
