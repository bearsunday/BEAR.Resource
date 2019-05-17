<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
