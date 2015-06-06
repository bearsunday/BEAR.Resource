<?php

use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Injector;

/* @var $loader ClassLoader */
$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
$dir = dirname(__DIR__) . '/Sandbox/src';
$loader->addPsr4('Sandbox\Demo03\\', $dir);
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$injector = new Injector(new ResourceModule('Sandbox\Demo03'));
$resource = $injector->getInstance(ResourceInterface::class);

/* @var $resource \BEAR\Resource\ResourceInterface */
return $resource;
