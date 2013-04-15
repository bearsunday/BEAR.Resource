<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Ray\Di\Definition;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Forge;
use Ray\Di\Container;
use Ray\Di\Injector;
use Ray\Di\EmptyModule;
use Doctrine\Common\Annotations\AnnotationReader;

$injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))), new EmptyModule);
$scheme = new SchemeCollection;
$scheme->scheme('app')->host('self')->toAdapter(
    new \BEAR\Resource\Adapter\App($injector, 'testworld', 'ResourceObject')
);
$factory = new Factory($scheme);

$invoker = new Invoker(
    new Linker(new AnnotationReader),
    new NamedParams(
        new SignalParam(
            new Manager(new HandlerFactory, new ResultFactory, new ResultCollection),
            new Param
        )
    ),
    new Logger
);
$resource = new Resource($factory, $invoker, new Request($invoker));

return $resource;
