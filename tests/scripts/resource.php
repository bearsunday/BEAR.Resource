<?php
use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\Factory;
use BEAR\Resource\Invoker;
use BEAR\Resource\Linker;
use BEAR\Resource\Resource;
use BEAR\Resource\Request;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\ReflectiveParams;

use Ray\Di\Definition;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Forge;
use Ray\Di\Container;
use Ray\Di\Manager;
use Ray\Di\Injector;
use Ray\Di\EmptyModule;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

$injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new Reader)))), new EmptyModule);
$namespace = array('self' => 'testworld');
$scheme = new SchemeCollection;
$scheme->scheme('app')->host('self')->toAdapter(
    new \BEAR\Resource\Adapter\App($injector, 'testworld', 'ResourceObject')
);
$factory = new Factory($scheme);
$signal = require dirname(dirname(__DIR__)) . '/vendor/aura/signal/scripts/instance.php';
$invoker = new Invoker(new Linker(new Reader), new ReflectiveParams(new Config(new Annotation(new Definition, new Reader)), $signal));
$resource = new Resource($factory, $invoker, new Request($invoker));

return $resource;
