<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

final class UserList extends ResourceObject
{
    public function __construct(
        private ResourceInterface $resource
    ) {}

    #[Link(rel: "self", href: "app://self/user/list?page={page}")]
    #[Link(rel: "next", href: "app://self/user/list?page={page + 1}")]
    #[Embed(rel: "users", src: "app://self/user?id={users.*.id}")]
    #[Embed(rel: "profiles", src: "app://self/user/profile?user_id={users.*.id}")]
    public function onGet(int $page = 1, bool $embed_profiles = true): self
    {
        // Simulate getting user IDs (this would be a real database query)
        $userIds = range(1, 20); // Get 20 users
        
        $users = [];
        
        // N+1 Problem: Making individual resource calls for each user
        foreach ($userIds as $userId) {
            $user = $this->resource->get("app://self/user", ["id" => $userId]);
            $users[] = $user->body;
            
            // Additional N+1: Get profile for each user individually
            if ($embed_profiles) {
                $profile = $this->resource->get("app://self/user/profile", ["user_id" => $userId]);
                $users[count($users) - 1]["profile"] = $profile->body;
            }
            
            // Simulate external API calls for permissions (first 5 users only)
            if ($userId <= 5) {
                $permissions = $this->resource->get("app://self/user/permissions", ["user_id" => $userId]);
                $users[count($users) - 1]["permissions"] = $permissions->body;
            }
        }
        
        $this->body = [
            "users" => $users,
            "total" => count($users),
            "page" => $page,
            "resource_calls" => count($userIds) * 2 + 5, // User + Profile + Permissions calls
            "hypermedia_links" => 2,
            "embedded_resources" => count($users)
        ];
        
        return $this;
    }
}