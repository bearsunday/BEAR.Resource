<?php

use \Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

// vendor & annotation
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->add('BEAR\Resource', [__DIR__]);
$loader->add('Sandbox', [__DIR__]);
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('noinspection');

$dir = sys_get_temp_dir();
ini_set('error_log', $dir . '/error.log');

$GLOBALS['RESOURCE'] = require __DIR__ . '/scripts/resource.php';
