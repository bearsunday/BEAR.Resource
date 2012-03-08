<?php
use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource,
    BEAR\Resource\Factory,
    BEAR\Resource\Invoker,
    BEAR\Resource\Linker,
    BEAR\Resource\Client,
    BEAR\Resource\Request,
    BEAR\Resource\SchemeCollection;

use Ray\Di\Definition,
    Ray\Di\Annotation,
    Ray\Di\Config,
    Ray\Di\Forge,
    Ray\Di\Container,
    Ray\Di\Manager,
    Ray\Di\Injector,
    Ray\Di\EmptyModule;

$injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))), new EmptyModule);
$namespace = array('self' => 'testworld');
$scheme = new SchemeCollection;
$scheme->scheme('app')->host('self')->toAdapter(new \BEAR\Resource\Adapter\App($injector, 'testworld', 'ResourceObject'));
$factory = new Factory($scheme);
$signal = require dirname(dirname(__DIR__)) . '/vendor/Aura/Signal/scripts/instance.php';
$invoker = new Invoker(new Config(new Annotation(new Definition)), new Linker, $signal);
$resource = new Client($factory, $invoker, new Request($invoker));
return $resource;
