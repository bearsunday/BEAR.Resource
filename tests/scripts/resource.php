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
use Guzzle\Parser\UriTemplate\UriTemplate;

$injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))), new EmptyModule);
$scheme = new SchemeCollection;
$scheme
->scheme('app')
->host('self')
->toAdapter(new Adapter\App($injector, 'Sandbox', 'Resource\App'));
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

$resource = new Resource(
    $factory,
    $invoker,
    new Request($invoker),
    new Anchor(new UriTemplate, new AnnotationReader, new Request($invoker))
);

return $resource;
