<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\App\Class\UserProfile;
use FakeVendor\Sandbox\Resource\App\Class\UserRegistration;

class InputTest extends ResourceObject
{
    public function onPost(#[Input] UserProfile $profile): static
    {
        $this->body = [
            'name' => $profile->firstName . ' ' . $profile->lastName,
            'age' => $profile->age,
            'email' => $profile->email,
        ];
        
        return $this;
    }
    
    public function onPut(#[Input] UserRegistration $registration): static
    {
        $this->body = [
            'user' => [
                'name' => $registration->firstName . ' ' . $registration->lastName,
                'age' => $registration->age,
            ],
            'address' => [
                'prefecture' => $registration->address->prefecture,
                'city' => $registration->address->city,
                'street' => $registration->address->street,
                'zipCode' => $registration->address->zipCode,
            ],
        ];
        
        return $this;
    }
}