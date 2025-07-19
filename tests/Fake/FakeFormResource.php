<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake;

use BEAR\Resource\ResourceObject;
use Ray\InputQuery\Attribute\Input;

class FakeFormResource extends ResourceObject
{
    public function onPost(
        #[Input] string $name,
        #[Input] ?string $email,
        string $notAnInput = 'default'
    ): void {
    }
}