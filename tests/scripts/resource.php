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
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use PHPParser_PrettyPrinter_Default;
use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_BuilderFactory;
use Ray\Di\Logger as DiLogger;

$injector = new Injector(
    new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))),
    new EmptyModule,
    new Bind,
    new Compiler(
        sys_get_temp_dir(),
        new PHPParser_PrettyPrinter_Default,
        new PHPParser_Parser(new PHPParser_Lexer),
        new PHPParser_BuilderFactory
    ),
    new DiLogger
);

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
