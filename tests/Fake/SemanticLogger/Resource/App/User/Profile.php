<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\ResourceObject;

final class UserProfile extends ResourceObject
{
    public function onGet(int $user_id): self
    {
        // Simulate database profile lookup
        usleep(8000); // 8ms per profile query
        
        $this->body = [
            "id" => $user_id,
            "user_id" => $user_id,
            "bio" => "Bio for user {$user_id}",
            "avatar_url" => "https://example.com/avatar{$user_id}.jpg"
        ];
        
        return $this;
    }
}