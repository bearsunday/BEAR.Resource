<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\ResourceObject;

final class UserPermissions extends ResourceObject
{
    public function onGet(int $user_id): self
    {
        // Simulate external API call delay
        usleep(50000); // 50ms external API delay
        
        $this->body = [
            "user_id" => $user_id,
            "permissions" => ["read", "write"],
            "last_login" => "2025-01-01 10:00:00"
        ];
        
        return $this;
    }
}