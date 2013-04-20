<?php
use \Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

// vendor & annotation
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('noinspection');

// library
require dirname(__DIR__) . '/src.php';
// tests
require __DIR__ . '/src.php';

$dir = sys_get_temp_dir();
ini_set('error_log', $dir . '/error.log');
