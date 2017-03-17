<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Injector;

$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
$loader->add('', dirname(__DIR__));
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$injector = new Injector(new ResourceModule('Sandbox'));
$resource = $injector->getInstance(ResourceInterface::class);

/* @var $resource \BEAR\Resource\ResourceInterface */
return $resource;
