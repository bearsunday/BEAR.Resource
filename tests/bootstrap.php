<?php

require dirname(__DIR__) . '/vendor/autoload.php';

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
