<?php

use BEAR\Resource\Adapter\App;
use BEAR\Resource\SchemeCollection;
use Ray\Di\Injector;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';

$loader->add('', dirname(__DIR__));
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
$loader->add('', dirname(__DIR__));

$resource = require dirname(dirname(dirname(dirname(__DIR__)))) . '/scripts/instance.php';
/* @var $resource \BEAR\Resource\Resource */
$scheme = (new SchemeCollection)->scheme('app')->host('self')->toAdapter(new App(Injector::create(), 'Sandbox', 'Resource\App'));
$resource->setSchemeCollection($scheme);
/* @var $resource \BEAR\Resource\Resource */
return $resource;
