<?php
use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource,
    BEAR\Resource\Factory,
    BEAR\Resource\Invoker,
    BEAR\Resource\Linker,
    BEAR\Resource\Client,
    BEAR\Resource\Request;

use Ray\Di\Annotation,
    Ray\Di\Config,
    Ray\Di\Forge,
    Ray\Di\Container,
    Ray\Di\Manager,
    Ray\Di\Injector,
    Ray\Di\EmptyModule;

$injector = new Injector(new Container(new Forge(new Config(new Annotation))), new EmptyModule);
$namespace = array('self' => 'testworld');
$resourceAdapters = array(
                'app' => new \BEAR\Resource\Adapter\App($injector, $namespace),
);
$factory = new Factory($injector, $resourceAdapters);
$invoker = new Invoker(new Config, new Linker);
$resource = new Client($factory, $invoker, new Request($invoker));
return $resource;