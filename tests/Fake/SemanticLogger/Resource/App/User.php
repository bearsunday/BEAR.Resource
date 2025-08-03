<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

final class User extends ResourceObject
{
    public function __construct(
        private ResourceInterface $resource
    ) {}

    #[Link(rel: "profile", href: "app://self/user/profile?user_id={id}")]
    #[Link(rel: "permissions", href: "app://self/user/permissions?user_id={id}")]
    public function onGet(int $id): self
    {
        // Simulate database access - this would be a real query in production
        usleep(5000); // 5ms per user query
        
        $this->body = [
            "id" => $id,
            "name" => "User {$id}",
            "email" => "user{$id}@example.com",
            "active" => 1
        ];
        
        return $this;
    }
}