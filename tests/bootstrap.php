<?php
use \Doctrine\Common\Annotations\AnnotationRegistry;

// vendor
require dirname(__DIR__) . '/vendor/.composer/autoload.php';
// library
require dirname(__DIR__) . '/src.php';
// tests
require __DIR__ . '/src.php';

// annotation "silent" loader
AnnotationRegistry::registerAutoloadNamespace('BEAR\Resource\Annotation', dirname(__DIR__) . '/src');
AnnotationRegistry::registerAutoloadNamespace('Ray\Di\Di', dirname(__DIR__) . '/vendor/Ray/Di/src');