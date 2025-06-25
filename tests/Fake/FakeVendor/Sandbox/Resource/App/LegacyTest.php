<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\App\Class\LegacyUser;

// Test resource without #[Input] attributes - should behave like ClassParam
class LegacyTest extends ResourceObject
{
    public function onPost(LegacyUser $user): static
    {
        $this->body = [
            'name' => $user->name,
            'age' => $user->age,
            'email' => $user->email,
        ];
        
        return $this;
    }
    
    public function onPut(LegacyUser $user, LegacyUser $admin): static
    {
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