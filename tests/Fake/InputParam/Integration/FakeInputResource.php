<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam\Integration;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;

final class FakeInputResource extends ResourceObject
{
    /**
     * Get user information
     *
     * @param FakeUserData $user   User data
     * @param string       $format Response format
     */
    public function onGet(
        #[Input] FakeUserData $user,
        string                $format = 'json',
    ): static {
        return $this;
    }
}
