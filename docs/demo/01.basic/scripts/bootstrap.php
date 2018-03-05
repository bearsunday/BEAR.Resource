<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use Composer\Autoload\ClassLoader;

/** @var $loader ClassLoader */
$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require dirname(__DIR__) . '/Sandbox/src/Resource/App/User.php';
