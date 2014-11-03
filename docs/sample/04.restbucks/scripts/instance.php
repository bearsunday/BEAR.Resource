<?php

use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Injector;

$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
$loader->add('', dirname(__DIR__));
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$injector = new Injector(new ResourceModule('Restbucks'));
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');

/* @var $resource \BEAR\Resource\ResourceInterface */
return $resource;
