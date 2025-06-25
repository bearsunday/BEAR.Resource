<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\App\Class\CamelCaseEntity;

class CamelCaseTest extends ResourceObject
{
    public function onPost(#[Input] CamelCaseEntity $entity): static
    {
        $this->body = [
            'firstName' => $entity->firstName,
            'lastName' => $entity->lastName,
            'fullName' => $entity->fullName,
            'emailAddress' => $entity->emailAddress,
            'phoneNumber' => $entity->phoneNumber,
        ];
        
        return $this;
    }
}