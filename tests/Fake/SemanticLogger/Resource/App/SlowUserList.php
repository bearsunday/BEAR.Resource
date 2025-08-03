<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\ResourceObject;
use PDO;

final class SlowUserList extends ResourceObject
{
    public function onGet(int $page = 1): self
    {
        // Setup in-memory database with test data
        $pdo = new PDO("sqlite::memory:");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                active INTEGER DEFAULT 1
            )
        ");
        
        $pdo->exec("
            CREATE TABLE profiles (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                bio TEXT,
                avatar_url TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Insert test data
        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $stmt->execute(["User {$i}", "user{$i}@example.com"]);
        }
        
        $stmt = $pdo->prepare("INSERT INTO profiles (user_id, bio, avatar_url) VALUES (?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $stmt->execute([$i, "Bio for user {$i}", "https://example.com/avatar{$i}.jpg"]);
        }
        
        // N+1 Query Problem: Get users then profile for each user
        $users = $pdo->query("SELECT * FROM users WHERE active = 1 LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as &$user) {
            // N+1 problem: One query per user
            $stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
            $stmt->execute([$user["id"]]);
            $user["profile"] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Simulate external API call for some users
            if ($user["id"] <= 5) {
                usleep(50000); // 50ms delay per API call
                $user["permissions"] = ["read", "write"];
            }
        }
        
        // Simulate some heavy computation
        usleep(100000); // 100ms additional processing
        
        $this->body = [
            "users" => $users,
            "total" => count($users),
            "page" => $page,
            "queries_executed" => 21, // 1 + 20 N+1 queries
            "api_calls" => 5
        ];
        
        return $this;
    }
}