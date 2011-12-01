<?php
namespace BEAR\Resource;

use Ray\Di\Annotation,
    Ray\Di\Config,
    Ray\Di\Forge,
    Ray\Di\Container,
    Ray\Di\Manager,
    Ray\Di\Injector,
    Ray\Di\EmptyModule,
    BEAR\Resource\Builder,
    BEAR\Resource\Mock\User;

$injector = new Injector(new Container(new Forge(new Config(new Annotation))), new EmptyModule);
$namespace = array('self' => 'testworld');
$resourceAdapters = array(
                'app' => new \BEAR\Resource\Adapter\App($injector, $namespace),
                'page' => new \BEAR\Resource\Adapter\Page($injector, $namespace)
);
$factory = new Factory($injector, $resourceAdapters);
$invoker = new Invoker(new Config, new Linker);
$resource = new Client($factory, $invoker, new Request($invoker));
return $resource;