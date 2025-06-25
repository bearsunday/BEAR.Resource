<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\App\Class\StructuredUser;

class StructuredInputTest extends ResourceObject
{
    public function onPost(#[Input(key: 'user')] StructuredUser $user): static
    {
        $this->body = [
            'name' => $user->name,
            'age' => $user->age,
            'email' => $user->email,
        ];
        
        return $this;
    }
    
    public function onPut(
        #[Input(key: 'user')] StructuredUser $user,
        #[Input(key: 'admin')] StructuredUser $admin,
    ): static {
        $this->body = [
            'user' => [
                'name' => $user->name,
                'age' => $user->age,
                'email' => $user->email,
            ],
            'admin' => [
                'name' => $admin->name,
                'age' => $admin->age,
                'email' => $admin->email,
            ],
        ];
        
        return $this;
    }
}