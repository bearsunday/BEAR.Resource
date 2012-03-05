<?php

use \Symfony\Component\ClassLoader\UniversalClassLoader;
use \Doctrine\Common\Annotations\AnnotationRegistry;

require __DIR__ . '/Aura.Di/src.php';
require __DIR__ . '/Ray.Aop/src.php';
require __DIR__ . '/Ray.Di/src.php';
require __DIR__ . '/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$classLoader = new UniversalClassLoader;
$classLoader->registerNamespaces([
    'Guzzle' => __DIR__ . '/Guzzle/src',
    'Doctrine' => __DIR__ . '/Doctrine/lib',
    'Monolog' => __DIR__ . '/Monolog/src',
    'Symfony' => __DIR__ . '/'
]);
$classLoader->registerPrefix('Zend_', __DIR__ . '/vendor');
$classLoader->register();

// annotation "silent" loader
AnnotationRegistry::registerAutoloadNamespace('BEAR\Resource\Annotation', dirname(__DIR__) . '/src');