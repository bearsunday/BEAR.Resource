<?php

use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ParamProvider\OnProvidesParam;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Params\UserId\SessionIdParam;
use Ray\Di\Injector;

$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
$loader->add('', dirname(__DIR__));
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$injector = Injector::create([new ResourceModule('Sandbox')]);
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');

/* @var $resource \BEAR\Resource\ResourceInterface */
$resource->attachParamProvider('user_id', new SessionIdParam);
$resource->attachParamProvider('*', new OnProvidesParam);
return $resource;

