<?php
namespace BEAR\Resource;

use Ray\Di\Definition;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Forge;
use Ray\Di\Container;
use Ray\Di\Injector;
use Ray\Di\EmptyModule;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;

$config = new Config(new Annotation(new Definition));
$injector = new Injector(new Container(new Forge($config)), new EmptyModule);
$scheme = new SchemeCollection;
$scheme->scheme('app')->host('self')->toAdapter(new \BEAR\Resource\Adapter\App($injector, 'testworld', 'App'));
$invoker = new Invoker(
    $config,
    new Linker,
    new Manager(
    new HandlerFactory, new ResultFactory, new ResultCollection)
);
$resource = new Resource(new Factory($scheme), $invoker, new Request($invoker));
return $resource;