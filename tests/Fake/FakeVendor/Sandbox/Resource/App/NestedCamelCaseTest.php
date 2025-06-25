<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\App\Class\NestedCamelCaseUser;

class NestedCamelCaseTest extends ResourceObject
{
    public function onPost(#[Input] NestedCamelCaseUser $user): static
    {
        $this->body = [
            'user' => [
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
            ],
            'address' => [
                'streetName' => $user->homeAddress->streetName,
                'cityName' => $user->homeAddress->cityName,
                'zipCode' => $user->homeAddress->zipCode,
            ],
        ];
        
        return $this;
    }
}