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

$tmp = sys_get_temp_dir();
ini_set('error_log', $tmp . '/error.log');
ini_set('xhprof.output_dir', $tmp);

$GLOBALS['RESOURCE'] = require __DIR__ . '/scripts/resource.php';
$_ENV['BEAR_TMP'] = __DIR__ . '/tmp';

$rm = function ($dir) use (&$rm) {
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? $rm($file) : unlink($file);
        @rmdir($file);
    }
};
$rm($_ENV['BEAR_TMP']);
