<?php

use \Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('BEAR\Resource\\', __DIR__);
$loader->addPsr4('BEAR\Resource\\', __DIR__ . '/Fake');
$loader->addPsr4('FakeVendor\Sandbox\\', __DIR__ . '/Fake/FakeVendor/Sandbox');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
$_ENV['PACKAGE_DIR'] = dirname(__DIR__);
$_ENV['TEST_DIR'] = __DIR__;
$rm = function ($dir) use (&$rm) {
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? $rm($file) : unlink($file);
        @rmdir($file);
    }
};
$rm($_ENV['TMP_DIR']);
