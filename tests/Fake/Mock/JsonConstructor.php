<?php

declare(strict_types=1);

namespace BEAR\Resource\Mock;

use BEAR\Resource\ResourceObject;

class JsonConstructor extends ResourceObject
{
    public function onGet(PersonConstructor $specialPerson, PersonConstructor $defaultPerson = null)
    {
        $this->body = $specialPerson;
        unset($defaultPerson);

        return $this;
    }
}
