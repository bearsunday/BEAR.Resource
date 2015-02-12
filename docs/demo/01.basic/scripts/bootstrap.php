<?php

use BEAR\Resource\Module\ResourceModule;
use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Injector;

/** @var $loader ClassLoader */
$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require dirname(__DIR__) . '/Sandbox/src/Resource/App/User.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
