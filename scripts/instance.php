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
use Doctrine\Common\Annotations\AnnotationReader as Reader;

require_once __DIR__ . '/instance/DefaultModule.php';

$config = new Config(new Annotation(new Definition));
$injector = new Injector(new Container(new Forge($config)), new DefaultModule);
$invoker = new Invoker(
    $config,
    new Linker(new Reader),
    new Manager(
    new HandlerFactory, new ResultFactory, new ResultCollection)
);
$resource = new Resource(new Factory(new SchemeCollection), $invoker, new Request($invoker));
return $resource;