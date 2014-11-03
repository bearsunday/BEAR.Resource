<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Injector;

$injector = new Injector;
$scheme = new SchemeCollection;
$scheme
    ->scheme('app')
    ->host('self')
    ->toAdapter(new Adapter\App($injector, 'TestVendor\Sandbox', 'Resource\App'));
$factory = new Factory($scheme);

$invoker = new Invoker(
    new Linker(new AnnotationReader),
    new NamedParameter(
        new SignalParameter(
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
    new Anchor(new AnnotationReader, new Request($invoker))
);

return $resource;
