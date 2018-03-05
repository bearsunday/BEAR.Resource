<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
$_ENV['schema_dir'] = __DIR__ . '/Fake/json_schema';
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
$unlink = function ($path) use (&$unlink) {
    foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
        is_dir($file) ? $unlink($file) : unlink($file);
        @rmdir($file);
    }
};
$unlink($_ENV['TMP_DIR']);
register_shutdown_function(function () use ($unlink) {
    $unlink($_ENV['TMP_DIR']);
});
