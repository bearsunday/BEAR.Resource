<?php

use \Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

// vendor & annotation
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('BEAR\Resource\\', __DIR__);
$loader->addPsr4('TestVendor\Sandbox\Resource\\', __DIR__ . '/TestVendor');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('noinspection');

$GLOBALS['RESOURCE'] = require __DIR__ . '/scripts/resource.php';
$GLOBALS['COMPILER'] = require __DIR__ . '/scripts/compiler.php';
$GLOBALS['INJECTOR'] = require __DIR__ . '/scripts/injector.php';

$_ENV['BEAR_TMP'] = __DIR__ . '/tmp';
$_ENV['PACKAGE_DIR'] = dirname(__DIR__);
$rm = function ($dir) use (&$rm) {
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? $rm($file) : unlink($file);
        @rmdir($file);
    }
};
$rm($_ENV['BEAR_TMP']);
