<?php

declare(strict_types=1);

namespace BEAR\Resource\Mock;

use BEAR\Resource\ResourceObject;

class Json extends ResourceObject
{
    public function onGet(Person $specialPerson, Person $defaultPerson = null)
    {
        $this->body = $specialPerson;
        unset($defaultPerson);

        return $this;
    }
}
