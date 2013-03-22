<?php
namespace BEAR\Resource;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Definition;
use Ray\Di\Injector;

$invoker = new Invoker(new Config(new Annotation(new Definition, new AnnotationReader)), new Linker(new AnnotationReader), new Manager(new HandlerFactory, new ResultFactory, new ResultCollection));
$invoker->setResourceLogger(new Logger);
$resource = new Resource(new Factory(new SchemeCollection), $invoker, new Request($invoker));

return $resource;