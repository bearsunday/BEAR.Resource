<?php

declare(strict_types=1);

namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\SemanticLog\Module\DevSemanticLoggerModule;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

class User extends ResourceObject
{
    protected $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    public function onGet(string $id): ResourceObject
    {
        usleep(10000); // simulate processing
        $this->body = $this->users[$id];

        return $this;
    }

    public function onPost(string $name, int $age): ResourceObject
    {
        usleep(20000); // simulate database operation
        $newId = count($this->users);
        $this->users[] = ['name' => $name, 'age' => $age, 'blog_id' => $newId];
        $this->code = 201;
        $this->body = $this->users[$newId];

        return $this;
    }
}

// Semantic logging with profiling
$module = new DevSemanticLoggerModule(__DIR__ . '/tmp/semantic-logs');
$module->install(new ResourceModule('MyVendor\Demo'));
$resource = (new Injector($module, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);

// GET request with profiling
$user = $resource->get('app://self/user', ['id' => '1']);

// POST request with profiling  
$newUser = $resource->post('app://self/user', ['name' => 'D\'Artagnan', 'age' => 18]);

// Display generated semantic log files
$logFiles = glob(__DIR__ . '/tmp/semantic-logs/*.json');
foreach ($logFiles as $file) {
    echo basename($file) . PHP_EOL;
}